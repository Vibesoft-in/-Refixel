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
        $sql = "SELECT b.*, s.name as service_name, s.starting_price, u.name as customer_name
                FROM bookings b
                JOIN services s ON b.service_id = s.id
                LEFT JOIN users u ON b.customer_id = u.id";

        $params = [];
        if ($status !== 'all') {
            $sql .= " WHERE b.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY b.created_at DESC";

        $enquiries = Database::fetchAll($sql, $params);
        $counts = [
            'new' => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM bookings WHERE status = 'new'")['c'] ?? 0),
            'all' => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM bookings")['c'] ?? 0),
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

