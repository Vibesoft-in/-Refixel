<?php
declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;

class NotificationController extends Controller
{
    public function recent(Request $request): Response
    {
        $user = Auth::user();
        if (!$user) {
            return $this->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $notifications = Database::fetchAll(
            "SELECT * FROM notifications WHERE to_address = :email OR to_address = :phone ORDER BY id DESC LIMIT 5",
            ['email' => $user['email'] ?? '', 'phone' => $user['phone'] ?? '']
        );

        return $this->json(['status' => 'success', 'notifications' => $notifications]);
    }
}
