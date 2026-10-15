<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\StaffEarning;

class PayoutController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status', 'pending');
        if (!in_array($status, ['pending', 'settled', 'all'], true)) {
            $status = 'pending';
        }

        $staffId = (int)$request->query('staff_id', 0);
        $month = trim((string)$request->query('month', ''));
        $search = trim((string)$request->query('search', ''));

        $payouts = StaffEarning::getAllWithDetails(
            $status, 
            $staffId > 0 ? $staffId : null, 
            $month ?: null, 
            $search ?: null
        );
        $stats = StaffEarning::getAdminPayoutStats();

        // Get technicians list for dropdown filter
        $technicians = Database::fetchAll(
            "SELECT id, name, phone, email FROM users WHERE role = 'staff' ORDER BY name ASC"
        );

        // Get distinct months for filter
        $months = Database::fetchAll(
            "SELECT DISTINCT month FROM staff_earnings WHERE month IS NOT NULL AND month != '' ORDER BY month DESC"
        );

        return $this->render('admin.payouts.index', [
            'title'       => 'Technician Payouts & Settlements | REFIXEL Admin',
            'payouts'     => $payouts,
            'stats'       => $stats,
            'technicians' => $technicians,
            'months'      => array_column($months, 'month'),
            'filters'     => [
                'status'   => $status,
                'staff_id' => $staffId,
                'month'    => $month,
                'search'   => $search,
            ],
        ], 'admin');
    }

    public function settle(Request $request, string $id): Response
    {
        $earningId = (int)$id;
        $method = trim((string)$request->input('settled_method', 'upi'));
        $ref = trim((string)$request->input('settled_ref', ''));
        $notes = trim((string)$request->input('settled_notes', ''));

        if ($earningId <= 0) {
            View::setFlash('error', 'Invalid payout record specified.');
            return $this->redirect('/admin/payouts');
        }

        $success = StaffEarning::settle($earningId, $method, $ref ?: null, $notes ?: null);
        if ($success) {
            $refText = $ref !== '' ? " (Ref: {$ref})" : '';
            View::setFlash('success', "Technician payout #{$earningId} has been successfully settled via " . strtoupper($method) . $refText . ".");
        } else {
            View::setFlash('error', 'Payout record could not be found or settled.');
        }

        return $this->redirect('/admin/payouts');
    }

    public function settleAll(Request $request, string $id): Response
    {
        $staffId = (int)$id;
        $method = trim((string)$request->input('settled_method', 'upi'));
        $ref = trim((string)$request->input('settled_ref', ''));
        $notes = trim((string)$request->input('settled_notes', ''));

        if ($staffId <= 0) {
            View::setFlash('error', 'Invalid technician specified.');
            return $this->redirect('/admin/payouts');
        }

        $tech = Database::fetchOne("SELECT name FROM users WHERE id = :id AND role = 'staff' LIMIT 1", ['id' => $staffId]);
        $techName = $tech['name'] ?? ('Technician #' . $staffId);

        $settledCount = StaffEarning::settleAllForTechnician($staffId, $method, $ref ?: null, $notes ?: null);
        if ($settledCount > 0) {
            View::setFlash('success', "Successfully settled {$settledCount} pending payout(s) for {$techName} via " . strtoupper($method) . ".");
        } else {
            View::setFlash('warning', "No pending payouts found for {$techName}.");
        }

        return $this->redirect('/admin/payouts');
    }
}
