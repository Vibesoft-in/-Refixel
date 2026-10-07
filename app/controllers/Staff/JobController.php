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
use App\Models\Job;
use App\Models\JobPhoto;
use App\Models\StaffEarning;
use App\Models\StatusHistory;

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

        return $this->render('staff.jobs.show', [
            'title'               => "Job #{$job['id']} - {$job['service_name']} | REFIXEL Staff",
            'job'                 => $job,
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

        return $this->render('staff.jobs.complete', [
            'title'        => "Complete Job #{$id} | REFIXEL Staff",
            'job'          => $job,
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

            // Execute workflow status transition
            Workflow::transition((int)$id, Workflow::STATUS_COMPLETED, $staffId, 'staff', $workSummary);

            // Calculate technician earnings commission (70% of service starting price or standard default)
            $basePrice = (float)($job['starting_price'] ?? 0);
            $commission = $basePrice > 0 ? round($basePrice * 0.70, 2) : 350.00;
            StaffEarning::recordJobCommission((int)$id, $staffId, $commission);

            View::setFlash('success', "Job #{$id} marked as completed successfully! Payout credited to your earnings.");
            return $this->redirect('/staff/jobs/' . $id);
        } catch (\Throwable $e) {
            View::setFlash('error', 'Failed to complete job: ' . $e->getMessage());
            return $this->redirect('/staff/jobs/' . $id . '/complete');
        }
    }
}

