<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Notifier;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\AbandonedCheckout;
use App\Models\Booking;

class DropoffController extends Controller
{
    public function index(Request $request): Response
    {
        $filter = (string)$request->query('filter', 'all');
        $search = trim((string)$request->query('q', ''));

        $dropoffs = AbandonedCheckout::getDropoffs($filter, $search);
        $stats    = AbandonedCheckout::getStats();

        return $this->render('admin.dropoffs.index', [
            'title'         => 'Abandoned Bookings & Payment Drop-offs | REFIXEL Admin',
            'dropoffs'      => $dropoffs,
            'stats'         => $stats,
            'currentFilter' => $filter,
            'search'        => $search,
        ], 'admin');
    }

    public function convertToCod(Request $request, string $id): Response
    {
        $dropoffId = (int)$id;
        $dropoff = Database::fetchOne("SELECT * FROM abandoned_checkouts WHERE id = :id LIMIT 1", ['id' => $dropoffId]);

        if (!$dropoff) {
            View::setFlash('error', 'Drop-off record not found.');
            return $this->redirect('/admin/dropoffs');
        }

        $bookingId = (int)($dropoff['booking_id'] ?? 0);
        if ($bookingId > 0) {
            $booking = Booking::find($bookingId);
            if ($booking) {
                // Switch booking to COD / Pay After Service
                Database::query(
                    "UPDATE bookings SET payment_type = 'cod', priority = 'normal', status = 'new', updated_at = NOW() WHERE id = :id",
                    ['id' => $bookingId]
                );

                // Cancel any pending online payment order
                Database::query(
                    "UPDATE payments SET status = 'pending' WHERE booking_id = :bid AND status = 'pending'",
                    ['bid' => $bookingId]
                );

                // Dispatch confirmation notifications
                try {
                    $bDetails = Booking::findWithDetails($bookingId);
                    if ($bDetails) {
                        Notifier::notifyNewBookingAdmin($bDetails);
                        Notifier::notifyBookingConfirmationCustomer($bDetails);
                    }
                } catch (\Throwable $e) {}
            }
        }

        Database::query(
            "UPDATE abandoned_checkouts SET is_recovered = 1, recovered_at = NOW(), notes = CONCAT(COALESCE(notes, ''), ' [Recovered & Converted to COD by Admin]') WHERE id = :id",
            ['id' => $dropoffId]
        );

        View::setFlash('success', "Customer booking #{$dropoff['booking_id']} successfully converted to 'Pay After Service (COD)'. Technician can now be assigned!");
        return $this->redirect('/admin/dropoffs');
    }

    public function markRecovered(Request $request, string $id): Response
    {
        $dropoffId = (int)$id;
        Database::query(
            "UPDATE abandoned_checkouts SET is_recovered = 1, recovered_at = NOW(), notes = CONCAT(COALESCE(notes, ''), ' [Marked Recovered by Admin]') WHERE id = :id",
            ['id' => $dropoffId]
        );

        View::setFlash('success', 'Lead marked as recovered.');
        return $this->redirect('/admin/dropoffs');
    }

    public function delete(Request $request, string $id): Response
    {
        $dropoffId = (int)$id;
        Database::query("DELETE FROM abandoned_checkouts WHERE id = :id", ['id' => $dropoffId]);

        View::setFlash('success', 'Drop-off record deleted.');
        return $this->redirect('/admin/dropoffs');
    }

    public function deleteAll(Request $request): Response
    {
        Database::query("DELETE FROM abandoned_checkouts");
        View::setFlash('success', 'All abandoned checkouts and drop-off records have been cleared.');
        return $this->redirect('/admin/dropoffs');
    }
}
