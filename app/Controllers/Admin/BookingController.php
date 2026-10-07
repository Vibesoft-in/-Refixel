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
use App\Models\JobPhoto;
use App\Models\StatusHistory;

class BookingController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status');
        $search = trim((string)$request->query('q', ''));

        $sql = "SELECT b.*, s.name as service_name, s.starting_price, u.name as customer_name,
                       st.name as staff_name, j.id as job_id, j.status as job_status
                FROM bookings b
                JOIN services s ON b.service_id = s.id
                LEFT JOIN users u ON b.customer_id = u.id
                LEFT JOIN jobs j ON b.id = j.booking_id
                LEFT JOIN users st ON j.staff_id = st.id
                WHERE 1=1";

        $params = [];

        if ($status && $status !== 'all') {
            $sql .= " AND b.status = :status";
            $params['status'] = $status;
        }

        if (!empty($search)) {
            $sql .= " AND (b.booking_no LIKE :q OR b.name LIKE :q OR b.phone LIKE :q OR s.name LIKE :q)";
            $params['q'] = "%{$search}%";
        }

        $sql .= " ORDER BY b.id DESC";

        $bookings = Database::fetchAll($sql, $params);

        $counts = [
            'all'         => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM bookings")['c'] ?? 0),
            'new'         => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM bookings WHERE status = 'new'")['c'] ?? 0),
            'assigned'    => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM bookings WHERE status = 'assigned'")['c'] ?? 0),
            'in_progress' => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM bookings WHERE status IN ('accepted', 'in_progress')")['c'] ?? 0),
            'completed'   => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM bookings WHERE status = 'completed'")['c'] ?? 0),
            'cancelled'   => (int)(Database::fetchOne("SELECT COUNT(*) as c FROM bookings WHERE status = 'cancelled'")['c'] ?? 0),
        ];

        return $this->render('admin.bookings.index', [
            'title'         => 'Manage Bookings & Jobs | REFIXEL Admin',
            'bookings'      => $bookings,
            'currentStatus' => $status ?? 'all',
            'search'        => $search,
            'counts'        => $counts,
        ], 'admin');
    }

    public function show(Request $request, string $id): Response
    {
        $booking = Booking::findWithDetails((int)$id);
        if (!$booking) {
            View::setFlash('error', 'Booking not found.');
            return $this->redirect('/admin/bookings');
        }

        $staffMembers = Database::fetchAll(
            "SELECT u.id, u.name, u.phone, sp.rating_avg, sp.is_available, sp.availability_note
             FROM users u
             LEFT JOIN staff_profiles sp ON u.id = sp.user_id
             WHERE u.role = 'staff' AND u.status = 'active'
             ORDER BY sp.is_available DESC, u.name ASC"
        );

        $job = Database::fetchOne("SELECT * FROM jobs WHERE booking_id = :bid", ['bid' => $id]);
        $history = $job ? StatusHistory::getByJob((int)$job['id']) : [];
        $jobPhotos = $job ? JobPhoto::getByJob((int)$job['id']) : [];
        $attachments = Database::fetchAll("SELECT * FROM booking_attachments WHERE booking_id = :bid", ['bid' => $id]);
        $payments = Database::fetchAll("SELECT * FROM payments WHERE booking_id = :bid ORDER BY id DESC", ['bid' => $id]);
        $invoice = Database::fetchOne("SELECT i.* FROM invoices i JOIN payments p ON i.payment_id = p.id WHERE p.booking_id = :bid LIMIT 1", ['bid' => $id]);

        return $this->render('admin.bookings.show', [
            'title'        => "Booking #{$booking['booking_no']} | REFIXEL Admin",
            'booking'      => $booking,
            'job'          => $job,
            'staffMembers' => $staffMembers,
            'history'      => $history,
            'jobPhotos'    => $jobPhotos,
            'attachments'  => $attachments,
            'payments'     => $payments,
            'invoice'      => $invoice,
        ], 'admin');
    }

    public function assignStaff(Request $request, string $id): Response
    {
        $staffId = (int)$request->input('staff_id');
        $scheduledAt = $request->input('scheduled_at');
        if (empty($scheduledAt)) {
            $scheduledAt = date('Y-m-d H:i:s');
        } else {
            $scheduledAt = str_replace('T', ' ', $scheduledAt) . ':00';
        }

        $booking = Booking::find((int)$id);
        if (!$booking) {
            View::setFlash('error', 'Booking not found.');
            return $this->redirect('/admin/bookings');
        }

        $job = Database::fetchOne("SELECT id, status, staff_id FROM jobs WHERE booking_id = :bid", ['bid' => $id]);

        if ($job) {
            $oldStaffId = (int)$job['staff_id'];
            Database::query(
                "UPDATE jobs SET staff_id = :sid, scheduled_at = :sched, status = 'assigned', updated_at = NOW() WHERE id = :jid",
                ['sid' => $staffId, 'sched' => $scheduledAt, 'jid' => $job['id']]
            );

            Database::query(
                "INSERT INTO status_history (job_id, from_status, to_status, changed_by, notes, created_at)
                 VALUES (:jid, :from, 'assigned', :uid, :notes, NOW())",
                [
                    'jid'   => $job['id'],
                    'from'  => $job['status'],
                    'uid'   => $this->userId(),
                    'notes' => "Admin reassigned job from staff #{$oldStaffId} to staff #{$staffId}",
                ]
            );
        } else {
            Job::create([
                'booking_id'   => (int)$id,
                'staff_id'     => $staffId,
                'status'       => 'assigned',
                'scheduled_at' => $scheduledAt,
            ]);
            $newJobId = (int)Database::lastInsertId();

            Database::query(
                "INSERT INTO status_history (job_id, from_status, to_status, changed_by, notes, created_at)
                 VALUES (:jid, 'new', 'assigned', :uid, 'Admin created field job and assigned technician', NOW())",
                ['jid' => $newJobId, 'uid' => $this->userId()]
            );
        }

        Booking::update((int)$id, ['status' => 'assigned']);

        // Notify technician of assignment
        try {
            $staffUser = Database::fetchOne("SELECT name, email, phone FROM users WHERE id = :sid", ['sid' => $staffId]);
            $bookingWithDetails = Booking::findWithDetails((int)$id);
            if ($staffUser && $bookingWithDetails) {
                \App\Core\Notifier::notifyStaffAssigned($bookingWithDetails, $staffUser);
            }
        } catch (\Throwable $e) {
            \App\Core\Logger::error("Staff assignment notification error: " . $e->getMessage());
        }

        View::setFlash('success', 'Technician successfully assigned to booking.');

        return $this->redirect('/admin/bookings/' . $id);
    }

    public function reschedule(Request $request, string $id): Response
    {
        $booking = Booking::find((int)$id);
        if (!$booking) {
            View::setFlash('error', 'Booking not found.');
            return $this->redirect('/admin/bookings');
        }

        $newDate = $request->input('preferred_date');
        $newTime = $request->input('preferred_time');
        $reason = trim((string)$request->input('reason', 'Rescheduled by administrator'));

        Booking::update((int)$id, [
            'preferred_date' => $newDate,
            'preferred_time' => $newTime,
        ]);

        $job = Database::fetchOne("SELECT id FROM jobs WHERE booking_id = :bid", ['bid' => $id]);
        if ($job) {
            Database::query(
                "UPDATE jobs SET scheduled_at = :sched, updated_at = NOW() WHERE id = :jid",
                ['sched' => $newDate . ' 10:00:00', 'jid' => $job['id']]
            );

            Database::query(
                "INSERT INTO status_history (job_id, from_status, to_status, changed_by, notes, created_at)
                 VALUES (:jid, 'rescheduled', 'rescheduled', :uid, :notes, NOW())",
                [
                    'jid'   => $job['id'],
                    'uid'   => $this->userId(),
                    'notes' => "Rescheduled to {$newDate} ({$newTime}). Reason: {$reason}",
                ]
            );
        }

        View::setFlash('success', "Booking #{$booking['booking_no']} rescheduled to {$newDate} successfully.");
        return $this->redirect('/admin/bookings/' . $id);
    }

    public function updateStatus(Request $request, string $id): Response
    {
        $booking = Booking::find((int)$id);
        if (!$booking) {
            View::setFlash('error', 'Booking not found.');
            return $this->redirect('/admin/bookings');
        }

        $newStatus = (string)$request->input('status');
        $notes = trim((string)$request->input('notes', 'Status updated by admin'));

        if (!in_array($newStatus, ['new', 'assigned', 'accepted', 'in_progress', 'completed', 'cancelled'], true)) {
            View::setFlash('error', 'Invalid status provided.');
            return $this->redirect('/admin/bookings/' . $id);
        }

        Booking::update((int)$id, ['status' => $newStatus]);

        $job = Database::fetchOne("SELECT id, status FROM jobs WHERE booking_id = :bid", ['bid' => $id]);
        if ($job) {
            Database::query("UPDATE jobs SET status = :st, updated_at = NOW() WHERE id = :jid", [
                'st'  => in_array($newStatus, ['assigned', 'accepted', 'in_progress', 'completed'], true) ? $newStatus : $job['status'],
                'jid' => $job['id'],
            ]);

            Database::query(
                "INSERT INTO status_history (job_id, from_status, to_status, changed_by, notes, created_at)
                 VALUES (:jid, :from, :to, :uid, :notes, NOW())",
                [
                    'jid'   => $job['id'],
                    'from'  => $job['status'],
                    'to'    => $newStatus,
                    'uid'   => $this->userId(),
                    'notes' => $notes,
                ]
            );
        }

        View::setFlash('success', "Booking status updated to '{$newStatus}'.");
        return $this->redirect('/admin/bookings/' . $id);
    }

    public function calendar(Request $request): Response
    {
        $month = $request->query('month', date('Y-m'));
        $jobs = Database::fetchAll(
            "SELECT b.id as booking_id, b.booking_no, b.name as customer_name, b.preferred_date, b.preferred_time,
                    b.status as booking_status, s.name as service_name, st.name as staff_name
             FROM bookings b
             JOIN services s ON b.service_id = s.id
             LEFT JOIN jobs j ON b.id = j.booking_id
             LEFT JOIN users st ON j.staff_id = st.id
             WHERE b.preferred_date LIKE :m
             ORDER BY b.preferred_date ASC, b.preferred_time ASC",
            ['m' => "{$month}%"]
        );

        return $this->render('admin.bookings.calendar', [
            'title' => 'Dispatch & Booking Calendar | REFIXEL Admin',
            'month' => $month,
            'jobs'  => $jobs,
        ], 'admin');
    }
}

