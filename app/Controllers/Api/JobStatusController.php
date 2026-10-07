<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Workflow;
use App\Models\Job;

class JobStatusController extends Controller
{
    public function update(Request $request): Response
    {
        $jobId = (int)$request->input('job_id');
        $newStatus = (string)$request->input('status');
        $notes = $request->input('notes') ? (string)$request->input('notes') : null;

        $role = Auth::role();
        $userId = Auth::id();

        // Enforce technician ownership if staff
        if ($role === 'staff') {
            $job = Job::findWithDetailsForStaff($jobId, $userId);
            if (!$job) {
                return $this->json(['status' => 'error', 'message' => 'Unauthorized for this job.'], 403);
            }
        }

        try {
            Workflow::transition($jobId, $newStatus, $userId, $role, $notes);
            return $this->json(['status' => 'success', 'message' => 'Status updated successfully.', 'status_code' => $newStatus]);
        } catch (\Throwable $e) {
            return $this->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}
