<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Controllers\BookingController;
use App\Controllers\SuperAdmin\GatewayController;
use App\Models\User;

class SecurityPhase2Task6Test
{
    private PDO $pdo;
    private int $passed = 0;
    private int $failed = 0;

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public function run(): void
    {
        echo "\n=== SecurityPhase2Task6Test: Config Check & Secret Leak Prevention ===\n";

        $this->testEnvExamplePlaceholders();
        $this->testCheckoutResponseExcludesKeySecret();
        $this->testGatewaysViewExcludesSecretsAndToggleMask();

        echo "\nResults: {$this->passed} passed, {$this->failed} failed\n";
        if ($this->failed > 0) {
            exit(1);
        }
    }

    private function testEnvExamplePlaceholders(): void
    {
        $envExamplePath = __DIR__ . '/../../.env.example';
        $content = file_get_contents($envExamplePath);

        $hasKeyId = str_contains($content, 'RAZORPAY_KEY_ID=');
        $hasKeySecret = str_contains($content, 'RAZORPAY_KEY_SECRET=');
        $hasWebhookSecret = str_contains($content, 'RAZORPAY_WEBHOOK_SECRET=');

        // Verify no live credentials in .env.example
        $hasLiveKey = str_contains($content, 'rzp_live_');

        if ($hasKeyId && $hasKeySecret && $hasWebhookSecret && !$hasLiveKey) {
            echo "  ✓ PASS: .env.example contains placeholders for KEY_ID, KEY_SECRET, and WEBHOOK_SECRET without secrets\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: .env.example is missing required Razorpay placeholders or leaks live credentials\n";
            $this->failed++;
        }
    }

    private function testCheckoutResponseExcludesKeySecret(): void
    {
        // Simulate checkout submit
        $req = new Request([], [
            'name'           => 'Checkout Security User',
            'phone'          => '9876543210',
            'address'        => 'Checkout Test Address',
            'city'           => 'Dehradun',
            'preferred_date' => date('Y-m-d', strtotime('+2 days')),
            'preferred_time' => '10:00 AM - 12:00 PM',
            'service_id'     => 1,
            'payment_method' => 'online',
            'is_ajax'        => 1,
        ], ['REQUEST_METHOD' => 'POST', 'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest']);

        $controller = new BookingController();
        $res = $controller->submit($req);
        $body = $res->getContent();
        $data = json_decode($body, true);

        // Clean up created booking
        if (!empty($data['booking_id'])) {
            $bId = (int)$data['booking_id'];
            $this->pdo->exec("DELETE FROM payments WHERE booking_id = {$bId}");
            $this->pdo->exec("DELETE FROM booking_items WHERE booking_id = {$bId}");
            $this->pdo->exec("DELETE FROM bookings WHERE id = {$bId}");
        }

        $hasKeyId = !empty($data['razorpay_key_id']);
        $leaksKeySecret = isset($data['key_secret'])
            || isset($data['razorpay_key_secret'])
            || str_contains($body, 'key_secret')
            || str_contains($body, 'webhook_secret');

        if ($hasKeyId && !$leaksKeySecret) {
            echo "  ✓ PASS: Checkout response provides public razorpay_key_id and strictly excludes key_secret\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: Checkout response leaks secret or is missing razorpay_key_id\n";
            $this->failed++;
        }
    }

    private function testGatewaysViewExcludesSecretsAndToggleMask(): void
    {
        // Authenticate as Super Admin
        $superAdmin = User::findBy('role', 'super_admin');
        $_SESSION['user'] = $superAdmin;

        $gwController = new GatewayController();
        $res = $gwController->index(new Request([]));
        $html = $res->getContent();

        $hasToggleMask = str_contains($html, 'toggleMask');
        $hasKeySecretValue = (bool)preg_match('/name=["\']key_secret["\']\s+value=["\'][^"\']+["\']/', $html);
        $hasWebhookSecretValue = (bool)preg_match('/name=["\']webhook_secret["\']\s+value=["\'][^"\']+["\']/', $html);

        if (!$hasToggleMask && !$hasKeySecretValue && !$hasWebhookSecretValue) {
            echo "  ✓ PASS: Gateway management view completely removes toggleMask and renders zero secrets to browser\n";
            $this->passed++;
        } else {
            echo "  ✗ FAIL: Gateway view contains toggleMask or renders secret values in HTML\n";
            $this->failed++;
        }
    }
}

(new SecurityPhase2Task6Test())->run();
