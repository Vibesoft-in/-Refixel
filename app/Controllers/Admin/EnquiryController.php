<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Core\Workflow;
use App\Models\Booking;
use App\Models\Job;

class EnquiryController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status', 'new');
        $sql = "SELECT b.*, s.name as service_name, s.starting_price, u.name as customer_name,
                       (SELECT p.status FROM payments p WHERE p.booking_id = b.id AND p.status = 'paid' ORDER BY p.id DESC LIMIT 1) as payment_paid_status,
                       (SELECT p.amount FROM payments p WHERE p.booking_id = b.id AND p.status = 'paid' ORDER BY p.id DESC LIMIT 1) as payment_paid_amount
                FROM bookings b
                JOIN services s ON b.service_id = s.id
                LEFT JOIN users u ON b.customer_id = u.id
                WHERE b.id NOT IN (SELECT booking_id FROM abandoned_checkouts WHERE is_recovered = 0 AND booking_id IS NOT NULL)";

        $params = [];
        if ($status !== 'all') {
            $sql .= " AND b.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY b.created_at DESC";

        $enquiries = Database::fetchAll($sql, $params);
        $counts = [
            'new' => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM bookings WHERE status = 'new' AND id NOT IN (SELECT booking_id FROM abandoned_checkouts WHERE is_recovered = 0 AND booking_id IS NOT NULL)")['c'] ?? 0),
            'all' => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM bookings WHERE id NOT IN (SELECT booking_id FROM abandoned_checkouts WHERE is_recovered = 0 AND booking_id IS NOT NULL)")['c'] ?? 0),
        ];

        return $this->render('admin.enquiries.index', [
            'title'     => 'Customer Enquiries | REFIXEL Admin',
            'enquiries' => $enquiries,
            'status'    => $status,
            'counts'    => $counts,
        ], 'admin');
    }

    public function show(Request $request, string $id): Response
    {
        $booking = Booking::findWithDetails((int)$id);
        if (!$booking) {
            View::setFlash('error', 'Enquiry not found.');
            return $this->redirect('/admin/enquiries');
        }

        // Prevent opening unrecovered payment drop-offs in enquiries; redirect to dropoffs desk
        $isDropoff = Database::fetchOne(
            "SELECT id FROM abandoned_checkouts WHERE booking_id = :bid AND is_recovered = 0 LIMIT 1",
            ['bid' => (int)$id]
        );
        if ($isDropoff) {
            View::setFlash('warning', "Booking #{$booking['booking_no']} is an unconfirmed payment drop-off. Please follow up or recover it from Abandoned & Drop-offs.");
            return $this->redirect('/admin/dropoffs');
        }

        $staffMembers = Database::fetchAll(
            "SELECT u.id, u.name, u.phone, sp.rating_avg, sp.is_available, sp.availability_note
             FROM users u
             LEFT JOIN staff_profiles sp ON u.id = sp.user_id
             WHERE u.role = 'staff' AND u.status = 'active'
             ORDER BY sp.is_available DESC, u.name ASC"
        );

        $attachments = Database::fetchAll("SELECT * FROM booking_attachments WHERE booking_id = :bid", ['bid' => $id]);

        return $this->render('admin.enquiries.show', [
            'title'        => "Enquiry #{$booking['booking_no']} | REFIXEL Admin",
            'booking'      => $booking,
            'staffMembers' => $staffMembers,
            'attachments'  => $attachments,
        ], 'admin');
    }

    public function update(Request $request, string $id): Response
    {
        $booking = Booking::find((int)$id);
        if (!$booking) {
            View::setFlash('error', 'Enquiry not found.');
            return $this->redirect('/admin/enquiries');
        }

        $priority = $request->input('priority', 'normal');
        $adminNotes = trim((string)$request->input('admin_notes'));
        $status = $request->input('status');

        $updates = [
            'priority'    => in_array($priority, ['low', 'normal', 'high', 'urgent'], true) ? $priority : 'normal',
            'admin_notes' => $adminNotes,
        ];

        if ($status && in_array($status, ['new', 'assigned', 'cancelled'], true)) {
            $updates['status'] = $status;
        }

        Booking::update((int)$id, $updates);
        View::setFlash('success', "Enquiry #{$booking['booking_no']} updated successfully.");

        return $this->redirect('/admin/enquiries/' . $id);
    }
}

