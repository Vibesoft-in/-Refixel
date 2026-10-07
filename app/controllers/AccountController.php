<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\User;

class AccountController extends Controller
{
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $recentBookings = Booking::findByCustomer($user['id']);

        return $this->render('customer.account.index', [
            'title'    => 'My Account | REFIXEL',
            'user'     => $user,
            'bookings' => array_slice($recentBookings, 0, 5),
        ], $request->isAjax() ? null : 'customer_account');
    }

    public function bookings(Request $request): Response
    {
        $bookings = Booking::findByCustomer(Auth::id());
        return $this->render('customer.account.bookings', [
            'title'    => 'My Bookings | REFIXEL',
            'bookings' => $bookings,
        ], $request->isAjax() ? null : 'customer_account');
    }

    public function bookingDetail(Request $request, string $id): Response
    {
        $booking = Booking::findWithDetails((int)$id);

        if (!$booking || (int)$booking['customer_id'] !== Auth::id()) {
            return $this->render('partials.404', ['title' => 'Booking Not Found'], $request->isAjax() ? null : 'customer_account')->setStatusCode(404);
        }

        $payments = \App\Core\Database::fetchAll(
            "SELECT * FROM payments WHERE booking_id = :bid ORDER BY id DESC",
            ['bid' => $booking['id']]
        );

        $invoices = \App\Core\Database::fetchAll(
            "SELECT i.*, p.method, p.amount as payment_amount
             FROM invoices i
             JOIN payments p ON i.payment_id = p.id
             WHERE p.booking_id = :bid
             ORDER BY i.id DESC",
            ['bid' => $booking['id']]
        );

        $review = \App\Models\Review::findByBooking((int)$booking['id']);

        return $this->render('customer.account.booking-detail', [
            'title'    => "Booking #{$booking['booking_no']} | REFIXEL",
            'booking'  => $booking,
            'payments' => $payments,
            'invoices' => $invoices,
            'review'   => $review,
        ], $request->isAjax() ? null : 'customer_account');
    }

    public function invoiceDetail(Request $request, string $id): Response
    {
        $invoice = Invoice::findWithDetails((int)$id);

        if (!$invoice || (int)$invoice['customer_id'] !== Auth::id()) {
            return $this->render('partials.404', ['title' => 'Invoice Not Found'], $request->isAjax() ? null : 'customer_account')->setStatusCode(404);
        }

        return $this->render('customer.account.invoice-detail', [
            'title'   => "GST Invoice #{$invoice['invoice_no']} | REFIXEL",
            'invoice' => $invoice,
        ], $request->isAjax() ? null : 'customer_account');
    }

    public function cancelBooking(Request $request, string $id): Response
    {
        $booking = Booking::find((int)$id);
        if (!$booking || (int)$booking['customer_id'] !== Auth::id()) {
            \App\Core\View::setFlash('error', 'Booking not found or unauthorized.');
            return $this->redirect('/account/bookings');
        }

        if (!\App\Core\Workflow::canTransition((string)$booking['status'], \App\Core\Workflow::STATUS_CANCELLED, 'customer')) {
            \App\Core\View::setFlash('error', "Booking in status '{$booking['status']}' cannot be cancelled.");
            return $this->redirect('/account/bookings/' . $id);
        }

        $reason = trim((string)$request->input('reason', 'Cancelled by customer'));

        try {
            \App\Core\Workflow::transitionBooking(
                (int)$id,
                \App\Core\Workflow::STATUS_CANCELLED,
                Auth::id(),
                'customer',
                $reason
            );
            \App\Core\View::setFlash('success', 'Your booking has been cancelled.');
        } catch (\Throwable $e) {
            \App\Core\View::setFlash('error', 'Cancellation failed: ' . $e->getMessage());
        }

        return $this->redirect('/account/bookings/' . $id);
    }

    public function rescheduleBooking(Request $request, string $id): Response
    {
        $booking = Booking::find((int)$id);
        if (!$booking || (int)$booking['customer_id'] !== Auth::id()) {
            \App\Core\View::setFlash('error', 'Booking not found or unauthorized.');
            return $this->redirect('/account/bookings');
        }

        if (!\App\Core\Workflow::canTransition((string)$booking['status'], \App\Core\Workflow::STATUS_RESCHEDULED, 'customer')) {
            \App\Core\View::setFlash('error', "Booking in status '{$booking['status']}' cannot be rescheduled.");
            return $this->redirect('/account/bookings/' . $id);
        }

        $preferredDate = trim((string)$request->input('preferred_date'));
        $preferredTime = trim((string)$request->input('preferred_time', '09:00 AM - 12:00 PM'));

        if (empty($preferredDate) || strtotime($preferredDate) < strtotime(date('Y-m-d'))) {
            \App\Core\View::setFlash('error', 'Please choose a valid upcoming service date.');
            return $this->redirect('/account/bookings/' . $id);
        }

        try {
            \App\Core\Database::query(
                "UPDATE bookings SET preferred_date = :d, preferred_time = :t, updated_at = NOW() WHERE id = :id",
                ['d' => $preferredDate, 't' => $preferredTime, 'id' => (int)$id]
            );

            \App\Core\Workflow::transitionBooking(
                (int)$id,
                \App\Core\Workflow::STATUS_RESCHEDULED,
                Auth::id(),
                'customer',
                "Rescheduled by customer to {$preferredDate} ({$preferredTime})"
            );

            \App\Core\View::setFlash('success', "Booking rescheduled to {$preferredDate} successfully.");
        } catch (\Throwable $e) {
            \App\Core\View::setFlash('error', 'Rescheduling failed: ' . $e->getMessage());
        }

        return $this->redirect('/account/bookings/' . $id);
    }

    public function requestRefund(Request $request, string $id): Response
    {
        $booking = Booking::find((int)$id);
        if (!$booking || (int)$booking['customer_id'] !== Auth::id()) {
            \App\Core\View::setFlash('error', 'Booking not found or unauthorized.');
            return $this->redirect('/account/bookings');
        }

        if (!\App\Core\Workflow::canTransition((string)$booking['status'], \App\Core\Workflow::STATUS_REFUND_REQUESTED, 'customer')) {
            \App\Core\View::setFlash('error', "Refund cannot be requested for booking in '{$booking['status']}' status.");
            return $this->redirect('/account/bookings/' . $id);
        }

        $paid = \App\Core\Database::fetchOne(
            "SELECT id, amount FROM payments WHERE booking_id = :bid AND status = 'paid' LIMIT 1",
            ['bid' => (int)$id]
        );

        if (!$paid) {
            \App\Core\View::setFlash('error', 'No paid transactions were found for this booking.');
            return $this->redirect('/account/bookings/' . $id);
        }

        $reason = trim((string)$request->input('reason', 'Customer refund request'));

        try {
            \App\Core\Workflow::transitionBooking(
                (int)$id,
                \App\Core\Workflow::STATUS_REFUND_REQUESTED,
                Auth::id(),
                'customer',
                $reason
            );

            \App\Core\View::setFlash('success', 'Refund request submitted. Our accounts team will review and process your refund within 2-3 business days.');
        } catch (\Throwable $e) {
            \App\Core\View::setFlash('error', 'Refund request failed: ' . $e->getMessage());
        }

        return $this->redirect('/account/bookings/' . $id);
    }

    public function submitReview(Request $request, string $id): Response
    {
        $booking = Booking::find((int)$id);
        if (!$booking || (int)$booking['customer_id'] !== Auth::id()) {
            \App\Core\View::setFlash('error', 'Booking not found or unauthorized.');
            return $this->redirect('/account/bookings');
        }

        if (!in_array((string)$booking['status'], ['completed', 'invoiced', 'reviewed'], true)) {
            \App\Core\View::setFlash('error', 'Reviews can only be submitted after service completion.');
            return $this->redirect('/account/bookings/' . $id);
        }

        if (\App\Models\Review::hasCustomerReviewed((int)$id)) {
            \App\Core\View::setFlash('error', 'You have already submitted a review for this booking.');
            return $this->redirect('/account/bookings/' . $id);
        }

        $rating = (int)$request->input('rating', 5);
        $comment = trim((string)$request->input('comment', ''));

        if ($rating < 1 || $rating > 5) {
            \App\Core\View::setFlash('error', 'Please provide a valid rating between 1 and 5 stars.');
            return $this->redirect('/account/bookings/' . $id);
        }

        if (empty($comment)) {
            \App\Core\View::setFlash('error', 'Please share a brief comment about your service experience.');
            return $this->redirect('/account/bookings/' . $id);
        }

        $job = \App\Core\Database::fetchOne(
            "SELECT staff_id FROM jobs WHERE booking_id = :bid LIMIT 1",
            ['bid' => (int)$id]
        );

        \App\Models\Review::create([
            'booking_id'  => (int)$id,
            'customer_id' => Auth::id(),
            'staff_id'    => !empty($job['staff_id']) ? (int)$job['staff_id'] : null,
            'rating'      => $rating,
            'comment'     => $comment,
            'is_approved' => 0,
        ]);

        if (in_array((string)$booking['status'], ['completed', 'invoiced'], true)) {
            try {
                \App\Core\Workflow::transitionBooking(
                    (int)$id,
                    \App\Core\Workflow::STATUS_REVIEWED,
                    Auth::id(),
                    'customer',
                    'Customer submitted rating and review'
                );
            } catch (\Throwable $e) {
                // Non-fatal if workflow status doesn't advance
            }
        }

        \App\Core\View::setFlash('success', 'Thank you! Your review has been submitted for moderation.');
        return $this->redirect('/account/bookings/' . $id);
    }

    public function invoices(Request $request): Response
    {
        $invoices = Invoice::findByCustomer(Auth::id());
        return $this->render('customer.account.invoices', [
            'title'    => 'My Invoices | REFIXEL',
            'invoices' => $invoices,
        ], $request->isAjax() ? null : 'customer_account');
    }

    public function profile(Request $request): Response
    {
        $userId = Auth::id();
        $customerProfile = \App\Core\Database::fetchOne("SELECT * FROM customer_profiles WHERE user_id = ?", [$userId]);

        if (!$customerProfile) {
            $latestBooking = \App\Core\Database::fetchOne("SELECT address, pincode FROM bookings WHERE customer_id = ? ORDER BY id DESC LIMIT 1", [$userId]);
            if ($latestBooking) {
                $customerProfile = [
                    'address' => $latestBooking['address'],
                    'city'    => 'Gurugram',
                    'pincode' => $latestBooking['pincode'],
                ];
            }
        }

        return $this->render('customer.account.profile', [
            'title'   => 'Profile Settings | REFIXEL',
            'user'    => Auth::user(),
            'profile' => $customerProfile,
        ], $request->isAjax() ? null : 'customer_account');
    }

    public function updateProfile(Request $request): Response
    {
        $userId = Auth::id();
        $name = trim((string)$request->input('name'));
        $email = $request->input('email') ? trim((string)$request->input('email')) : null;
        $address = trim((string)$request->input('address'));
        $city = trim((string)$request->input('city'));
        $pincode = trim((string)$request->input('pincode'));
        $state = trim((string)$request->input('state', 'Haryana'));
        $house_no = trim((string)$request->input('house_no'));
        $street = trim((string)$request->input('street'));
        $address_type = trim((string)$request->input('address_type', 'Home'));

        if (empty($name)) {
            \App\Core\View::setFlash('error', 'Full Name is required.');
            return $this->redirect('/account/profile');
        }

        try {
            // Update User table
            \App\Core\Database::execute("UPDATE users SET name = ?, email = ? WHERE id = ?", [$name, $email, $userId]);

            // Check if profile exists
            $existingProfile = \App\Core\Database::fetchOne("SELECT id FROM customer_profiles WHERE user_id = ?", [$userId]);
            if ($existingProfile) {
                \App\Core\Database::execute(
                    "UPDATE customer_profiles SET address = ?, city = ?, pincode = ?, state = ?, house_no = ?, street = ?, address_type = ? WHERE user_id = ?",
                    [$address, $city, $pincode, $state, $house_no, $street, $address_type, $userId]
                );
            } else {
                \App\Core\Database::execute(
                    "INSERT INTO customer_profiles (user_id, address, city, pincode, state, house_no, street, address_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [$userId, $address, $city, $pincode, $state, $house_no, $street, $address_type]
                );
            }

            // Update session data
            $user = Auth::user();
            $user['name'] = $name;
            $user['email'] = $email;
            Auth::login($user);

            \App\Core\View::setFlash('success', 'Profile updated successfully.');
        } catch (\Throwable $e) {
            \App\Core\View::setFlash('error', 'Failed to update profile.');
        }

        return $this->redirect('/account/profile');
    }

    public function privacy(Request $request): Response
    {
        $userId = Auth::id();
        $consents = \App\Models\Consent::getByUser($userId);

        return $this->render('customer.account.privacy', [
            'title'    => 'Privacy & Data Rights | REFIXEL',
            'user'     => Auth::user(),
            'consents' => $consents,
        ], $request->isAjax() ? null : 'customer_account');
    }

    public function exportData(Request $request): Response
    {
        $userId = Auth::id();
        $user = User::find($userId);
        if ($user) {
            unset($user['password_hash']);
        }

        $profile = \App\Models\CustomerProfile::findBy('user_id', $userId);
        $consents = \App\Models\Consent::getByUser($userId);
        $bookings = Booking::findByCustomer($userId);
        $invoices = Invoice::findByCustomer($userId);
        $reviews = \App\Core\Database::fetchAll(
            "SELECT * FROM reviews WHERE customer_id = :uid",
            ['uid' => $userId]
        );

        $exportData = [
            'export_metadata' => [
                'platform'     => 'REFIXEL Service Platform',
                'compliance'   => 'Digital Personal Data Protection (DPDP) Act Foundation',
                'generated_at' => date('c'),
                'user_id'      => $userId,
            ],
            'account'  => $user,
            'profile'  => $profile,
            'consents' => $consents,
            'bookings' => $bookings,
            'invoices' => $invoices,
            'reviews'  => $reviews,
        ];

        $response = Response::json($exportData, 200);
        $filename = 'REFIXEL_data_export_' . $userId . '_' . date('Ymd_His') . '.json';
        $response->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        return $response;
    }

    public function requestDataDeletion(Request $request): Response
    {
        $userId = Auth::id();
        $user = User::find($userId);
        $reason = trim((string)$request->input('reason', 'Customer requested account and data erasure'));

        // Log compliance audit entry
        \App\Core\Logger::info("DPDP Data Erasure Request submitted by customer #{$userId}", [
            'user_id' => $userId,
            'reason'  => $reason,
            'ip'      => $request->getIp(),
        ]);

        // Revoke marketing/general consents
        \App\Models\Consent::record($userId, 'erasure_requested_' . date('Ymd'), $request->getIp());

        // Notify Administrator of pending privacy request
        \App\Core\Notifier::notifyAdminAlert(
            'DPDP Data Erasure Request',
            "Customer #{$userId} ({$user['name']}, {$user['phone']}) has formally requested personal data deletion. Reason: {$reason}",
            ['user_id' => $userId, 'ip' => $request->getIp()]
        );

        \App\Core\View::setFlash('success', 'Your data erasure request has been formally recorded under the DPDP Act. Our compliance officer will process your request within the statutory timeframe (subject to statutory financial record retention requirements).');
        return $this->redirect('/account/privacy');
    }
}

