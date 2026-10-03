<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Database;
use App\Core\InvoiceBuilder;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Booking;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function index(Request $request): Response
    {
        $payments = Database::fetchAll(
            "SELECT p.*, b.booking_no, b.name as customer_name, b.phone as customer_phone,
                    s.name as service_name, i.invoice_no, i.id as invoice_id, i.total as invoice_total
             FROM payments p
             JOIN bookings b ON p.booking_id = b.id
             JOIN services s ON b.service_id = s.id
             LEFT JOIN invoices i ON p.id = i.payment_id
             ORDER BY p.id DESC"
        );

        $unpaidBookings = Database::fetchAll(
            "SELECT b.id, b.booking_no, b.name, s.starting_price, s.name as service_name
             FROM bookings b
             JOIN services s ON b.service_id = s.id
             LEFT JOIN payments p ON b.id = p.booking_id AND p.status = 'paid'
             WHERE p.id IS NULL
             ORDER BY b.id DESC"
        );

        $totalPaid = (float)(Database::fetchOne("SELECT IFNULL(SUM(amount), 0) as s FROM payments WHERE status = 'paid'")['s'] ?? 0);
        $totalPending = (float)(Database::fetchOne("SELECT IFNULL(SUM(amount), 0) as s FROM payments WHERE status = 'pending'")['s'] ?? 0);
        $totalGst = (float)(Database::fetchOne("SELECT IFNULL(SUM(gst_amount), 0) as s FROM invoices")['s'] ?? 0);

        return $this->render('admin.payments.index', [
            'title'          => 'Payments & Invoices | Primodomus Admin',
            'payments'       => $payments,
            'unpaidBookings' => $unpaidBookings,
            'totalPaid'      => $totalPaid,
            'totalPending'   => $totalPending,
            'totalGst'       => $totalGst,
        ], 'admin');
    }

    public function recordPayment(Request $request): Response
    {
        $bookingId = (int)$request->input('booking_id');
        $amount = (float)$request->input('amount');
        $method = (string)$request->input('method', 'cash');
        $txnRef = trim((string)$request->input('transaction_ref', ''));

        if ($bookingId <= 0 || $amount <= 0) {
            View::setFlash('error', 'Please select a valid booking and amount.');
            return $this->redirect('/admin/payments');
        }

        $booking = Booking::find($bookingId);
        if (!$booking) {
            View::setFlash('error', 'Booking record not found.');
            return $this->redirect('/admin/payments');
        }

        $paymentId = Payment::create([
            'booking_id'      => $bookingId,
            'amount'          => $amount,
            'method'          => in_array($method, ['cash', 'upi', 'card', 'bank'], true) ? $method : 'cash',
            'status'          => 'paid',
            'transaction_ref' => !empty($txnRef) ? $txnRef : null,
            'paid_at'         => date('Y-m-d H:i:s'),
            'recorded_by'     => $this->userId(),
        ]);

        // Generate GST Invoice
        $invoice = InvoiceBuilder::createForPayment($paymentId, $amount);

        // Trigger invoice notification to customer
        try {
            \App\Core\Notifier::notifyInvoiceCreated($invoice, [
                'name'  => $booking['name'],
                'email' => $booking['email'] ?? null,
                'phone' => $booking['phone'],
            ]);
        } catch (\Throwable $e) {
            \App\Core\Logger::error("Invoice notification skipped: " . $e->getMessage());
        }

        View::setFlash('success', "Payment of ₹" . number_format($amount, 2) . " recorded and GST Invoice #{$invoice['invoice_no']} generated.");
        return $this->redirect('/admin/payments');
    }

    public function showInvoice(Request $request, string $id): Response
    {
        $invoice = Database::fetchOne(
            "SELECT i.*, p.method as payment_method, p.paid_at, p.transaction_ref,
                    b.booking_no, b.name as customer_name, b.phone as customer_phone,
                    b.email as customer_email, b.address as customer_address, b.pincode,
                    s.name as service_name
             FROM invoices i
             JOIN payments p ON i.payment_id = p.id
             JOIN bookings b ON p.booking_id = b.id
             JOIN services s ON b.service_id = s.id
             WHERE i.id = :id
             LIMIT 1",
            ['id' => $id]
        );

        if (!$invoice) {
            View::setFlash('error', 'Invoice not found.');
            return $this->redirect('/admin/payments');
        }

        return $this->render('admin.payments.invoice', [
            'title'   => "GST Invoice #{$invoice['invoice_no']} | Primodomus Admin",
            'invoice' => $invoice,
        ], 'admin');
    }

    public function refundPayment(Request $request, string $id): Response
    {
        $payment = Database::fetchOne(
            "SELECT p.*, b.id as booking_id, b.booking_no
             FROM payments p
             JOIN bookings b ON p.booking_id = b.id
             WHERE p.id = :id
             LIMIT 1",
            ['id' => $id]
        );

        if (!$payment) {
            View::setFlash('error', 'Payment record not found.');
            return $this->redirect('/admin/payments');
        }

        if ($payment['status'] !== 'paid') {
            View::setFlash('error', "Only paid payments can be refunded. Current status: {$payment['status']}");
            return $this->redirect('/admin/payments');
        }

        $reason = trim((string)$request->input('reason', 'Admin issued refund'));
        $amount = (float)$payment['amount'];

        // If online transaction reference exists, call gateway refund
        if (!empty($payment['transaction_ref'])) {
            try {
                $gateway = new \App\Services\Payment\RazorpayGateway();
                $gateway->refund($payment['transaction_ref'], $amount, $reason);
            } catch (\Throwable $e) {
                // Log and continue
            }
        }

        // Update payment status
        Database::query(
            "UPDATE payments SET status = 'refunded' WHERE id = :id",
            ['id' => $id]
        );

        // Transition booking to refunded
        try {
            \App\Core\Workflow::transitionBooking(
                (int)$payment['booking_id'],
                \App\Core\Workflow::STATUS_REFUNDED,
                $this->userId(),
                'admin',
                "Payment #{$id} of ₹" . number_format($amount, 2) . " refunded. Reason: {$reason}"
            );
        } catch (\Throwable $e) {
            // Non-fatal if workflow status already in compatible state
        }

        View::setFlash('success', "Refund of ₹" . number_format($amount, 2) . " processed successfully for Booking #{$payment['booking_no']}.");
        return $this->redirect('/admin/payments');
    }
}
