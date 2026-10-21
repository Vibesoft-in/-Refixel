<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Env;
use App\Core\InvoiceBuilder;
use App\Core\Logger;
use App\Core\Notifier;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Booking;
use PDO;

class WebhookController extends Controller
{
    public function razorpay(Request $request): Response
    {
        $rawPayload = $request->getRawBody();
        $signature = $request->getHeader('X-Razorpay-Signature')
            ?? ($_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '');

        // Secret strictly retrieved from environment or secured gateway setting
        $webhookSecret = (string)Env::get('RAZORPAY_WEBHOOK_SECRET', '');
        if (empty($webhookSecret)) {
            $webhookSecret = (string)\App\Models\Setting::get('razorpay_webhook_secret', '');
        }

        if (empty($rawPayload) || empty($signature) || empty($webhookSecret)) {
            Logger::error("Razorpay webhook rejected: missing payload, signature, or webhook secret configured");
            return Response::json(['error' => 'Invalid webhook request or signature verification failed'], 400);
        }

        // HMAC SHA256 signature verification over raw body
        $expectedSignature = hash_hmac('sha256', $rawPayload, $webhookSecret);
        if (!hash_equals($expectedSignature, $signature)) {
            Logger::error("Razorpay webhook HMAC signature mismatch: signature verification failed");
            return Response::json(['error' => 'Invalid webhook signature'], 400);
        }

        $data = json_decode($rawPayload, true);
        if (!is_array($data) || empty($data['event'])) {
            Logger::warning("Razorpay webhook malformed JSON payload");
            return Response::json(['error' => 'Malformed payload'], 400);
        }

        $event = (string)$data['event'];

        if ($event === 'payment.captured') {
            return $this->handlePaymentCaptured($data);
        }

        if ($event === 'payment.failed') {
            $paymentEntity = $data['payload']['payment']['entity'] ?? [];
            $orderId = (string)($paymentEntity['order_id'] ?? 'unknown');
            $paymentId = (string)($paymentEntity['id'] ?? 'unknown');
            $errorCode = (string)($paymentEntity['error_code'] ?? 'unknown');
            $errorDesc = (string)($paymentEntity['error_description'] ?? 'unknown');
            Logger::warning("Razorpay webhook payment.failed: order={$orderId}, payment={$paymentId}, error={$errorCode} - {$errorDesc}");
            return Response::json(['status' => 'logged'], 200);
        }

        if ($event === 'refund.processed') {
            $refundEntity = $data['payload']['refund']['entity'] ?? [];
            $refundId = (string)($refundEntity['id'] ?? 'unknown');
            $paymentId = (string)($refundEntity['payment_id'] ?? 'unknown');
            $amountInPaise = (int)($refundEntity['amount'] ?? 0);
            $amountInRupees = round($amountInPaise / 100, 2);
            Logger::info("Razorpay webhook refund.processed: refund_id={$refundId}, payment_id={$paymentId}, amount={$amountInRupees}");
            return Response::json(['status' => 'logged'], 200);
        }

        // Log unhandled events safely
        Logger::info("Razorpay webhook received unhandled event: {$event}");
        return Response::json(['status' => 'ignored'], 200);
    }

    private function handlePaymentCaptured(array $data): Response
    {
        $paymentEntity = $data['payload']['payment']['entity'] ?? [];
        $orderId = trim((string)($paymentEntity['order_id'] ?? ''));
        $paymentId = trim((string)($paymentEntity['id'] ?? ''));
        $amountInPaise = (int)($paymentEntity['amount'] ?? 0);
        $amountInRupees = round($amountInPaise / 100, 2);
        $currency = strtoupper(trim((string)($paymentEntity['currency'] ?? 'INR')));

        if (empty($orderId) || empty($paymentId)) {
            Logger::warning("Razorpay webhook payment.captured missing order_id or payment_id");
            return Response::json(['status' => 'ignored'], 200);
        }

        // Locate booking by gateway_order_id or linked pending payment
        $booking = Database::fetchOne("SELECT * FROM bookings WHERE gateway_order_id = ? LIMIT 1", [$orderId]);
        if (!$booking) {
            $paymentRef = Database::fetchOne("SELECT * FROM payments WHERE gateway_order_id = ? LIMIT 1", [$orderId]);
            if ($paymentRef && !empty($paymentRef['booking_id'])) {
                $booking = Booking::find((int)$paymentRef['booking_id']);
            }
        }

        if (!$booking) {
            Logger::warning("Razorpay webhook: booking record not found for gateway order {$orderId}");
            return Response::json(['status' => 'order_not_found'], 200);
        }

        $bookingId = (int)$booking['id'];

        // Strict Currency Check
        if ($currency !== 'INR') {
            Logger::warning("Razorpay webhook currency mismatch for booking #{$bookingId}: expected INR, got {$currency}");
            return Response::json(['status' => 'currency_mismatch'], 200);
        }

        // Calculate expected amount from booking
        $totalAmount = (float)($booking['total_amount'] ?? 0);
        if ($totalAmount <= 0) {
            $totalAmount = 899.00;
        }
        $paymentType = (string)($booking['payment_type'] ?? 'cod');
        $expectedAmount = ($paymentType === 'online') ? $totalAmount : 49.00;
        if ((float)($booking['advance_amount'] ?? 0) > 0) {
            $expectedAmount = (float)$booking['advance_amount'];
        }

        $pendingPayment = Database::fetchOne(
            "SELECT * FROM payments WHERE gateway_order_id = ? OR (booking_id = ? AND status = 'pending') ORDER BY id DESC LIMIT 1",
            [$orderId, $bookingId]
        );
        if ($pendingPayment && (float)$pendingPayment['amount'] > 0) {
            $expectedAmount = (float)$pendingPayment['amount'];
        }

        // Strict Amount Check
        if (abs($expectedAmount - $amountInRupees) > 0.01) {
            Logger::warning("Razorpay webhook amount mismatch for booking #{$bookingId}: expected {$expectedAmount}, got {$amountInRupees}");
            return Response::json(['status' => 'amount_mismatch'], 200);
        }

        // Atomic Payment State Transition & Idempotency
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        $isAlreadyPaid = false;
        $paymentIdDb = 0;

        try {
            $stmt = $pdo->prepare("SELECT id, status, amount FROM payments WHERE booking_id = ? AND (gateway_order_id = ? OR gateway_order_id IS NULL) ORDER BY id DESC LIMIT 1 FOR UPDATE");
            $stmt->execute([$bookingId, $orderId]);
            $paymentRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($paymentRow && $paymentRow['status'] === 'paid') {
                $isAlreadyPaid = true;
                $paymentIdDb = (int)$paymentRow['id'];
            } elseif ($paymentRow) {
                $updStmt = $pdo->prepare(
                    "UPDATE payments SET status = 'paid', method = 'upi', amount = ?, gateway_order_id = ?, gateway_payment_id = ?, transaction_ref = ?, paid_at = NOW() WHERE id = ? AND status = 'pending'"
                );
                $updStmt->execute([$amountInRupees, $orderId, $paymentId, $paymentId, $paymentRow['id']]);
                $affected = $updStmt->rowCount();
                $paymentIdDb = (int)$paymentRow['id'];
                if ($affected === 0) {
                    $isAlreadyPaid = true;
                }
            } else {
                $insStmt = $pdo->prepare(
                    "INSERT INTO payments (booking_id, amount, method, status, gateway_order_id, gateway_payment_id, transaction_ref, paid_at, recorded_by) VALUES (?, ?, 'upi', 'paid', ?, ?, ?, NOW(), ?)"
                );
                $insStmt->execute([$bookingId, $amountInRupees, $orderId, $paymentId, $paymentId, $booking['customer_id'] ?: null]);
                $paymentIdDb = (int)$pdo->lastInsertId();
            }

            // Update booking gateway_order_id if not previously set
            $pdo->prepare("UPDATE bookings SET gateway_order_id = COALESCE(gateway_order_id, ?) WHERE id = ?")
                ->execute([$orderId, $bookingId]);

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Logger::error("Razorpay webhook atomic transition failed for booking #{$bookingId}: " . $e->getMessage());
            return Response::json(['error' => 'Database update error'], 500);
        }

        // Idempotency check: If already paid, do not double-confirm or double-invoice
        if ($isAlreadyPaid) {
            Logger::info("Razorpay webhook: booking #{$bookingId} already marked paid, returning 200 OK");
            return Response::json(['status' => 'already_processed'], 200);
        }

        // Update booking financials
        $prio = ($paymentType === 'online') ? 'high' : 'normal';
        $dueAmount = max(0.00, round($totalAmount - $amountInRupees, 2));
        $bookingController = new BookingController();
        $bookingController->updateBookingFinancials($bookingId, $totalAmount, $amountInRupees, $dueAmount, $paymentType, $prio, 'new');

        // Generate GST Invoice
        $invoiceRecord = null;
        try {
            $existingInvoice = Database::fetchOne("SELECT * FROM invoices WHERE payment_id = ? LIMIT 1", [$paymentIdDb]);
            if (!$existingInvoice && $paymentIdDb > 0) {
                $subtotal = round($totalAmount / 1.18, 2);
                $invoiceRecord = InvoiceBuilder::createForPayment($paymentIdDb, $subtotal, 18.00, $amountInRupees, $dueAmount);
            } else {
                $invoiceRecord = $existingInvoice;
            }
        } catch (\Throwable $invErr) {
            Logger::error("Razorpay webhook invoice generation error: " . $invErr->getMessage());
        }

        // Send booking confirmation notifications
        try {
            $bookingRecord = [
                'id'             => $bookingId,
                'booking_id'     => $bookingId,
                'customer_id'    => $booking['customer_id'] ?? null,
                'booking_no'     => $booking['booking_no'],
                'service_name'   => 'Doorstep Service',
                'name'           => $booking['name'],
                'phone'          => $booking['phone'],
                'email'          => $booking['email'] ?? null,
                'address'        => $booking['address'],
                'city'           => $booking['pincode'] ? "Pincode: {$booking['pincode']}" : 'Service Area',
                'preferred_date' => (string)$booking['preferred_date'],
                'preferred_time' => (string)$booking['preferred_time'],
                'total_amount'   => $totalAmount,
                'advance_amount' => $amountInRupees,
                'due_amount'     => $dueAmount,
                'payment_type'   => $paymentType,
                'priority'       => $prio,
                'is_new_customer'=> false,
                'login_url'      => View::fullUrl('/login'),
                'admin_url'      => View::fullUrl('/admin/bookings/' . $bookingId),
            ];

            Notifier::notifyNewBookingAdmin($bookingRecord);
            Notifier::notifyBookingConfirmationCustomer($bookingRecord);

            if ($paymentType === 'online' && $invoiceRecord) {
                Notifier::notifyPrepaidInvoiceWorkDue($invoiceRecord, $bookingRecord);
            }
        } catch (\Throwable $notifyErr) {
            Logger::error("Razorpay webhook notification error: " . $notifyErr->getMessage());
        }

        return Response::json(['status' => 'success'], 200);
    }
}
