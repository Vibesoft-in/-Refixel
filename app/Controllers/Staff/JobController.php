<?php
declare(strict_types=1);

namespace App\Controllers\Staff;

use App\Controllers\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Upload;
use App\Core\View;
use App\Core\Workflow;
use App\Core\Database;
use App\Models\Job;
use App\Models\JobPhoto;
use App\Models\Payment;
use App\Models\StaffEarning;
use App\Models\StatusHistory;
use App\Services\Payment\RazorpayGateway;

class JobController extends Controller
{
    public function index(Request $request): Response
    {
        $staffId = Auth::id();
        $filter = $request->query('filter', 'today');
        if (!in_array($filter, ['today', 'upcoming', 'in_progress', 'completed', 'all'], true)) {
            $filter = 'today';
        }

        $jobs = Job::findByStaff($staffId, $filter);
        $counts = [
            'today'       => count(Job::findByStaff($staffId, 'today')),
            'upcoming'    => count(Job::findByStaff($staffId, 'upcoming')),
            'in_progress' => count(Job::findByStaff($staffId, 'in_progress')),
            'completed'   => count(Job::findByStaff($staffId, 'completed')),
            'all'         => count(Job::findByStaff($staffId, 'all')),
        ];

        return $this->render('staff.jobs.index', [
            'title'  => 'My Assigned Jobs | REFIXEL Staff',
            'jobs'   => $jobs,
            'filter' => $filter,
            'counts' => $counts,
        ], 'staff');
    }

    public function show(Request $request, string $id): Response
    {
        $staffId = Auth::id();
        $job = Job::findWithDetailsForStaff((int)$id, $staffId);
        if (!$job) {
            View::setFlash('error', 'Job not found or not assigned to your account.');
            return $this->redirect('/staff/jobs');
        }

        $history = StatusHistory::getByJob((int)$id);
        $photos = JobPhoto::getByJob((int)$id);
        $customerAttachments = Job::getCustomerAttachments((int)$job['booking_id']);

        $beforePhotos = array_filter($photos, fn($p) => $p['type'] === 'before');
        $afterPhotos = array_filter($photos, fn($p) => $p['type'] === 'after');

        $totAmt = (float)($job['total_amount'] > 0 ? $job['total_amount'] : ($job['starting_price'] ?? 0));
        $advAmt = (float)($job['advance_amount'] ?? 0);
        $dueAmt = (float)($job['due_amount'] > 0 ? $job['due_amount'] : max(0.0, $totAmt - $advAmt));
        if (!empty($job['is_prepaid'])) {
            $dueAmt = 0.0;
        }
        $payments = Database::fetchAll("SELECT * FROM payments WHERE booking_id = :bid ORDER BY id DESC", ['bid' => (int)$job['booking_id']]);

        return $this->render('staff.jobs.show', [
            'title'               => "Job #{$job['id']} - {$job['service_name']} | REFIXEL Staff",
            'job'                 => $job,
            'totAmt'              => $totAmt,
            'advAmt'              => $advAmt,
            'dueAmt'              => $dueAmt,
            'payments'            => $payments,
            'history'             => $history,
            'photos'              => $photos,
            'beforePhotos'        => $beforePhotos,
            'afterPhotos'         => $afterPhotos,
            'customerAttachments' => $customerAttachments,
        ], 'staff');
    }

    public function accept(Request $request, string $id): Response
    {
        $staffId = Auth::id();
        $job = Job::findWithDetailsForStaff((int)$id, $staffId);
        if (!$job) {
            View::setFlash('error', 'Job not found or not assigned to your account.');
            return $this->redirect('/staff/jobs');
        }

        try {
            Workflow::transition((int)$id, Workflow::STATUS_ACCEPTED, $staffId, 'staff', 'Technician accepted the assignment.');
            View::setFlash('success', 'Job #' . $id . ' accepted successfully. You can now notify customer when en route.');
        } catch (\Throwable $e) {
            View::setFlash('error', 'Could not accept job: ' . $e->getMessage());
        }

        return $this->redirect('/staff/jobs/' . $id);
    }

    public function onTheWay(Request $request, string $id): Response
    {
        $staffId = Auth::id();
        $job = Job::findWithDetailsForStaff((int)$id, $staffId);
        if (!$job) {
            View::setFlash('error', 'Job not found or not assigned to your account.');
            return $this->redirect('/staff/jobs');
        }

        try {
            Workflow::recordSubAction((int)$id, 'on_the_way', $staffId, 'Technician is en route to customer location.');
            View::setFlash('success', 'Status updated: You are on the way to the customer location.');
        } catch (\Throwable $e) {
            View::setFlash('error', 'Could not update status: ' . $e->getMessage());
        }

        return $this->redirect('/staff/jobs/' . $id);
    }

    public function start(Request $request, string $id): Response
    {
        $staffId = Auth::id();
        $job = Job::findWithDetailsForStaff((int)$id, $staffId);
        if (!$job) {
            View::setFlash('error', 'Job not found or not assigned to your account.');
            return $this->redirect('/staff/jobs');
        }

        try {
            Workflow::transition((int)$id, Workflow::STATUS_IN_PROGRESS, $staffId, 'staff', 'Technician arrived on site and started the job.');
            View::setFlash('success', 'Job started! Work is now in progress.');
        } catch (\Throwable $e) {
            View::setFlash('error', 'Could not start job: ' . $e->getMessage());
        }

        return $this->redirect('/staff/jobs/' . $id);
    }

    public function uploadPhoto(Request $request, string $id): Response
    {
        $staffId = Auth::id();
        $job = Job::findWithDetailsForStaff((int)$id, $staffId);
        if (!$job) {
            View::setFlash('error', 'Job not found or not assigned to your account.');
            return $this->redirect('/staff/jobs');
        }

        $type = $request->input('type', 'before');
        if (!in_array($type, ['before', 'after'], true)) {
            $type = 'before';
        }

        $file = $request->file('photo');
        if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            try {
                $meta = Upload::processWithMetadata($file, 'jobs');
                JobPhoto::addPhoto((int)$id, $type, $meta['file_path']);
                View::setFlash('success', ucfirst($type) . ' photo uploaded successfully.');
            } catch (\Throwable $e) {
                View::setFlash('error', 'Photo upload failed: ' . $e->getMessage());
            }
        } else {
            View::setFlash('error', 'Please choose a photo to upload.');
        }

        return $this->redirect('/staff/jobs/' . $id);
    }

    public function complete(Request $request, string $id): Response
    {
        $staffId = Auth::id();
        $job = Job::findWithDetailsForStaff((int)$id, $staffId);
        if (!$job) {
            View::setFlash('error', 'Job not found or not assigned to your account.');
            return $this->redirect('/staff/jobs');
        }

        if ($job['status'] === Workflow::STATUS_COMPLETED) {
            View::setFlash('info', 'This job has already been marked as completed.');
            return $this->redirect('/staff/jobs/' . $id);
        }

        $photos = JobPhoto::getByJob((int)$id);
        $beforePhotos = array_filter($photos, fn($p) => $p['type'] === 'before');
        $afterPhotos = array_filter($photos, fn($p) => $p['type'] === 'after');

        $totAmt = (float)($job['total_amount'] > 0 ? $job['total_amount'] : ($job['starting_price'] ?? 0));
        $advAmt = (float)($job['advance_amount'] ?? 0);
        $dueAmt = (float)($job['due_amount'] > 0 ? $job['due_amount'] : max(0.0, $totAmt - $advAmt));
        if (!empty($job['is_prepaid'])) {
            $dueAmt = 0.0;
        }
        $payments = Database::fetchAll("SELECT * FROM payments WHERE booking_id = :bid ORDER BY id DESC", ['bid' => (int)$job['booking_id']]);

        return $this->render('staff.jobs.complete', [
            'title'        => "Complete Job #{$id} | REFIXEL Staff",
            'job'          => $job,
            'totAmt'       => $totAmt,
            'advAmt'       => $advAmt,
            'dueAmt'       => $dueAmt,
            'payments'     => $payments,
            'beforePhotos' => $beforePhotos,
            'afterPhotos'  => $afterPhotos,
        ], 'staff');
    }

    public function processComplete(Request $request, string $id): Response
    {
        $staffId = Auth::id();
        $job = Job::findWithDetailsForStaff((int)$id, $staffId);
        if (!$job) {
            View::setFlash('error', 'Job not found or not assigned to your account.');
            return $this->redirect('/staff/jobs');
        }

        $validator = $this->validate($request, [
            'work_summary' => 'required|min:5',
        ]);

        if ($validator->fails()) {
            View::setFlash('error', $validator->firstError());
            return $this->redirect('/staff/jobs/' . $id . '/complete');
        }

        $workSummary = trim((string)$request->input('work_summary'));
        $finalNotes = trim((string)$request->input('final_notes'));

        // Handle before photos if uploaded on completion screen
        $beforeFiles = $request->file('before_photos');
        if ($beforeFiles && is_array($beforeFiles['name'] ?? null)) {
            $uploaded = Upload::processMultiple($beforeFiles, 'jobs');
            foreach ($uploaded as $meta) {
                JobPhoto::addPhoto((int)$id, 'before', $meta['file_path']);
            }
        } elseif ($beforeFiles && ($beforeFiles['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            try {
                $meta = Upload::processWithMetadata($beforeFiles, 'jobs');
                JobPhoto::addPhoto((int)$id, 'before', $meta['file_path']);
            } catch (\Throwable $e) {}
        }

        // Handle after photos if uploaded on completion screen
        $afterFiles = $request->file('after_photos');
        if ($afterFiles && is_array($afterFiles['name'] ?? null)) {
            $uploaded = Upload::processMultiple($afterFiles, 'jobs');
            foreach ($uploaded as $meta) {
                JobPhoto::addPhoto((int)$id, 'after', $meta['file_path']);
            }
        } elseif ($afterFiles && ($afterFiles['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            try {
                $meta = Upload::processWithMetadata($afterFiles, 'jobs');
                JobPhoto::addPhoto((int)$id, 'after', $meta['file_path']);
            } catch (\Throwable $e) {}
        }

        try {
            // Save work summary and final notes
            Job::saveCompletion((int)$id, $staffId, $workSummary, $finalNotes);

            // Verify or settle customer payment if submitted in completion form
            $totAmt = (float)($job['total_amount'] > 0 ? $job['total_amount'] : ($job['starting_price'] ?? 0));
            $advAmt = (float)($job['advance_amount'] ?? 0);
            $dueAmt = (float)($job['due_amount'] > 0 ? $job['due_amount'] : max(0.0, $totAmt - $advAmt));
            if (!empty($job['is_prepaid'])) {
                $dueAmt = 0.0;
            }

            $settleMethod = strtolower(trim((string)$request->input('settle_payment_method', '')));
            $settleAmount = (float)$request->input('settle_payment_amount', 0);
            if ($settleAmount > 0 && in_array($settleMethod, ['cash', 'upi', 'online'], true)) {
                $normMethod = ($settleMethod === 'online') ? 'upi' : $settleMethod;
                Payment::create([
                    'booking_id'      => (int)$job['booking_id'],
                    'amount'          => $settleAmount,
                    'method'          => $normMethod,
                    'status'          => 'paid',
                    'transaction_ref' => 'COMPLETION-' . strtoupper($normMethod) . '-' . time(),
                    'paid_at'         => date('Y-m-d H:i:s'),
                    'recorded_by'     => $staffId,
                ]);

                $dueAmt = max(0.00, round($dueAmt - $settleAmount, 2));
                $newAdv = round($advAmt + $settleAmount, 2);
                Database::query(
                    "UPDATE bookings SET due_amount = :due, advance_amount = :adv, updated_at = NOW() WHERE id = :bid",
                    ['due' => $dueAmt, 'adv' => $newAdv, 'bid' => (int)$job['booking_id']]
                );
            }

            // Enforce payment settlement before completion
            if ($dueAmt > 0 && empty($job['is_prepaid'])) {
                View::setFlash('error', 'Customer payment of ₹' . number_format($dueAmt, 0) . ' must be collected (via Cash or Online QR) before completing the job.');
                return $this->redirect('/staff/jobs/' . $id . '/complete');
            }

            // Execute workflow status transition
            Workflow::transition((int)$id, Workflow::STATUS_COMPLETED, $staffId, 'staff', $workSummary);

            // Calculate actual technician earnings and company split using CommissionRule
            $totAmt = (float)(($job['total_amount'] ?? 0) > 0 ? $job['total_amount'] : ($job['starting_price'] ?? 0));
            $materialCost = (float)($request->input('material_cost') ?? 0);
            $split = \App\Models\CommissionRule::calculateSplit(
                $totAmt,
                $materialCost,
                (int)($job['service_id'] ?? 0),
                (int)($job['category_id'] ?? 0)
            );
            $technicianPayout = (float)$split['worker_total_payout'];
            $adminFee = (float)$split['admin_share'];

            StaffEarning::recordJobCommission(
                (int)$id,
                $staffId,
                $technicianPayout,
                null,
                $totAmt,
                $materialCost,
                $adminFee
            );

            // Dispatch Final GST Invoice to customer upon job completion
            try {
                $bDetails = \App\Models\Booking::findWithDetails((int)$job['booking_id']);
                $latestPayment = Database::fetchOne(
                    "SELECT * FROM payments WHERE booking_id = :bid AND status = 'paid' ORDER BY id DESC LIMIT 1",
                    ['bid' => (int)$job['booking_id']]
                );

                $finalInvoice = null;
                if ($latestPayment) {
                    $finalInvoice = Database::fetchOne("SELECT * FROM invoices WHERE payment_id = :pid LIMIT 1", ['pid' => (int)$latestPayment['id']]);
                    if (!$finalInvoice) {
                        $subtotal = round($totAmt / 1.18, 2);
                        $finalInvoice = \App\Core\InvoiceBuilder::createForPayment((int)$latestPayment['id'], $subtotal, 18.00, $totAmt, 0.00);
                    } else {
                        Database::query(
                            "UPDATE invoices SET due_amount = 0.00, advance_paid = :adv WHERE id = :invid",
                            ['adv' => $totAmt, 'invid' => (int)$finalInvoice['id']]
                        );
                        $finalInvoice['due_amount'] = 0.00;
                        $finalInvoice['advance_paid'] = $totAmt;
                    }
                } else {
                    $pId = (int)Payment::create([
                        'booking_id'      => (int)$job['booking_id'],
                        'amount'          => $totAmt,
                        'method'          => 'cash',
                        'status'          => 'paid',
                        'transaction_ref' => 'COMPLETION-AUTO-' . time(),
                        'paid_at'         => date('Y-m-d H:i:s'),
                        'recorded_by'     => $staffId,
                    ]);
                    $subtotal = round($totAmt / 1.18, 2);
                    $finalInvoice = \App\Core\InvoiceBuilder::createForPayment($pId, $subtotal, 18.00, $totAmt, 0.00);
                }

                if ($finalInvoice && $bDetails) {
                    $staffUser = Database::fetchOne("SELECT name, email, phone FROM users WHERE id = :sid", ['sid' => $staffId]);
                    $bDetails['work_summary'] = $workSummary;
                    \App\Core\Notifier::notifyFinalCompletedInvoice($finalInvoice, $bDetails, $staffUser ?: ['name' => $job['staff_name'] ?? 'Technician']);
                }
            } catch (\Throwable $invEx) {
                \App\Core\Logger::error("Completion invoice dispatch error: " . $invEx->getMessage());
            }

            View::setFlash('success', "Job #{$id} marked as completed successfully! Payout credited to your earnings.");
            return $this->redirect('/staff/jobs/' . $id);
        } catch (\Throwable $e) {
            View::setFlash('error', 'Failed to complete job: ' . $e->getMessage());
            return $this->redirect('/staff/jobs/' . $id . '/complete');
        }
    }

    public function recordPayment(Request $request, string $id): Response
    {
        $staffId = Auth::id();
        $job = Job::findWithDetailsForStaff((int)$id, $staffId);
        if (!$job) {
            if ($request->isAjax()) {
                return Response::json(['success' => false, 'message' => 'Job not found.'], 404);
            }
            View::setFlash('error', 'Job not found or not assigned to your account.');
            return $this->redirect('/staff/jobs');
        }

        $method = strtolower(trim((string)$request->input('method', 'cash')));
        if (!in_array($method, ['cash', 'upi', 'online'], true)) {
            $method = 'cash';
        }
        $normalizedMethod = ($method === 'online') ? 'upi' : $method;

        $amount = (float)$request->input('amount', 0);
        $transactionRef = trim((string)$request->input('transaction_ref', ''));
        $notes = trim((string)$request->input('notes', ''));

        $totAmt = (float)($job['total_amount'] > 0 ? $job['total_amount'] : ($job['starting_price'] ?? 0));
        $advAmt = (float)($job['advance_amount'] ?? 0);
        $calcDue = max(0.0, round($totAmt - $advAmt, 2));
        if ($amount <= 0) {
            $amount = (float)($job['due_amount'] > 0 ? $job['due_amount'] : $calcDue);
        }

        if ($amount <= 0) {
            if ($request->isAjax()) {
                return Response::json(['success' => false, 'message' => 'Invalid payment amount.'], 400);
            }
            View::setFlash('error', 'Invalid payment amount.');
            return $this->redirect('/staff/jobs/' . $id);
        }

        try {
            Payment::create([
                'booking_id'      => (int)$job['booking_id'],
                'amount'          => $amount,
                'method'          => $normalizedMethod,
                'status'          => 'paid',
                'transaction_ref' => $transactionRef ?: ('STAFF-' . strtoupper($normalizedMethod) . '-' . time()),
                'paid_at'         => date('Y-m-d H:i:s'),
                'recorded_by'     => $staffId,
            ]);

            $currDue = (float)($job['due_amount'] > 0 ? $job['due_amount'] : $calcDue);
            $newDue = max(0.00, round($currDue - $amount, 2));
            $newAdv = round($advAmt + $amount, 2);

            Database::query(
                "UPDATE bookings SET due_amount = :due, advance_amount = :adv, updated_at = NOW() WHERE id = :bid",
                ['due' => $newDue, 'adv' => $newAdv, 'bid' => (int)$job['booking_id']]
            );

            Database::query(
                "INSERT INTO status_history (job_id, from_status, to_status, changed_by, notes, created_at)
                 VALUES (:jid, :from, :to, :uid, :notes, NOW())",
                [
                    'jid'   => (int)$id,
                    'from'  => $job['status'],
                    'to'    => $job['status'],
                    'uid'   => $staffId,
                    'notes' => "Technician recorded payment of ₹" . number_format($amount, 2) . " via " . strtoupper($normalizedMethod) . ($notes ? " ({$notes})" : ""),
                ]
            );

            if ($request->isAjax()) {
                return Response::json([
                    'success'    => true,
                    'message'    => "Payment of ₹" . number_format($amount, 0) . " recorded successfully!",
                    'amount'     => $amount,
                    'method'     => $normalizedMethod,
                    'due_amount' => $newDue,
                    'redirect'   => View::url('/staff/jobs/' . $id . '/complete'),
                ]);
            }

            View::setFlash('success', "Payment of ₹" . number_format($amount, 0) . " recorded successfully! Proceed to finalize completion.");
            return $this->redirect('/staff/jobs/' . $id . '/complete');
        } catch (\Throwable $e) {
            if ($request->isAjax()) {
                return Response::json(['success' => false, 'message' => 'Failed to record payment: ' . $e->getMessage()], 500);
            }
            View::setFlash('error', 'Failed to record payment: ' . $e->getMessage());
            return $this->redirect('/staff/jobs/' . $id);
        }
    }

    public function createPaymentOrder(Request $request, string $id): Response
    {
        $staffId = Auth::id();
        $job = Job::findWithDetailsForStaff((int)$id, $staffId);
        if (!$job) {
            return Response::json(['success' => false, 'message' => 'Job not found.'], 404);
        }

        $totAmt = (float)($job['total_amount'] > 0 ? $job['total_amount'] : ($job['starting_price'] ?? 0));
        $advAmt = (float)($job['advance_amount'] ?? 0);
        $dueAmt = (float)($job['due_amount'] > 0 ? $job['due_amount'] : max(0.0, $totAmt - $advAmt));

        if ($dueAmt <= 0) {
            return Response::json(['success' => false, 'message' => 'This job is already fully paid.'], 400);
        }

        try {
            $rzp = new RazorpayGateway();
            $order = $rzp->createOrder((int)$job['booking_id'], $dueAmt, 'INR', [
                'job_id'       => (string)$id,
                'booking_no'   => (string)$job['booking_no'],
                'service_name' => (string)$job['service_name'],
                'type'         => 'balance_collection_by_staff',
            ]);

            return Response::json([
                'success'           => true,
                'razorpay_order_id' => $order['order_id'],
                'razorpay_key_id'   => $rzp->getKeyId(),
                'amount'            => (int)round($dueAmt * 100),
                'amount_rupees'     => $dueAmt,
                'currency'          => 'INR',
                'booking_no'        => $job['booking_no'],
                'customer_name'     => $job['customer_name'],
                'customer_phone'    => $job['customer_phone'],
            ]);
        } catch (\Throwable $e) {
            return Response::json([
                'success'       => false,
                'message'       => 'Razorpay order failed: ' . $e->getMessage(),
                'amount_rupees' => $dueAmt,
            ], 500);
        }
    }
}

