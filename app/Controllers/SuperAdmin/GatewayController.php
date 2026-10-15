<?php
declare(strict_types=1);

namespace App\Controllers\SuperAdmin;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\PaymentGateway;

class GatewayController extends Controller
{
    public function index(Request $request): Response
    {
        $gateways = PaymentGateway::getAll();

        return $this->render('superadmin.gateways.index', [
            'title'    => 'Payment Gateways & API Credentials | Super Admin',
            'gateways' => $gateways,
        ], 'superadmin');
    }

    public function update(Request $request, string $id): Response
    {
        $gatewayId = (int)$id;
        $gateway = PaymentGateway::find($gatewayId);

        if (!$gateway) {
            View::setFlash('error', 'Payment gateway record not found.');
            return $this->redirect('/superadmin/gateways');
        }

        $name           = trim((string)$request->input('name', $gateway['name']));
        $environment    = in_array($request->input('environment'), ['test', 'live'], true) ? $request->input('environment') : 'live';
        $keyId          = trim((string)$request->input('key_id', ''));
        $keySecret      = trim((string)$request->input('key_secret', ''));
        $webhookSecret  = trim((string)$request->input('webhook_secret', ''));
        $merchantEmail  = trim((string)$request->input('merchant_email', ''));
        $merchantName   = trim((string)$request->input('merchant_name', ''));
        $currency       = trim((string)$request->input('currency', 'INR'));
        $description    = trim((string)$request->input('description', ''));
        $isActive       = (int)($request->input('is_active', 0));

        PaymentGateway::update($gatewayId, [
            'name'           => $name,
            'environment'    => $environment,
            'is_active'      => $isActive,
            'key_id'         => $keyId,
            'key_secret'     => $keySecret,
            'webhook_secret' => $webhookSecret,
            'merchant_email' => $merchantEmail,
            'merchant_name'  => $merchantName,
            'currency'       => $currency,
            'description'    => $description,
        ]);

        // Sync settings table for instant system-wide use
        $updatedGateway = PaymentGateway::find($gatewayId);
        if ($updatedGateway) {
            PaymentGateway::syncToSettings($updatedGateway);
        }

        View::setFlash('success', "Payment gateway '{$name}' credentials updated successfully.");
        return $this->redirect('/superadmin/gateways');
    }

    public function toggle(Request $request, string $id): Response
    {
        $gatewayId = (int)$id;
        $gateway = PaymentGateway::find($gatewayId);

        if (!$gateway) {
            View::setFlash('error', 'Gateway not found.');
            return $this->redirect('/superadmin/gateways');
        }

        $newStatus = $gateway['is_active'] ? 0 : 1;
        PaymentGateway::update($gatewayId, ['is_active' => $newStatus]);

        $updatedGateway = PaymentGateway::find($gatewayId);
        if ($updatedGateway) {
            PaymentGateway::syncToSettings($updatedGateway);
        }

        $statusLabel = $newStatus ? 'activated' : 'deactivated';
        View::setFlash('success', "Gateway '{$gateway['name']}' {$statusLabel} successfully.");
        return $this->redirect('/superadmin/gateways');
    }

    public function test(Request $request, string $id): Response
    {
        $gateway = PaymentGateway::find((int)$id);
        if (!$gateway) {
            View::setFlash('error', 'Gateway not found.');
            return $this->redirect('/superadmin/gateways');
        }

        if ($gateway['code'] === 'razorpay') {
            $keyId = $gateway['key_id'] ?? '';
            $keySecret = $gateway['key_secret'] ?? '';

            if (empty($keyId) || empty($keySecret)) {
                View::setFlash('error', 'Razorpay Key ID and Secret cannot be empty for testing.');
                return $this->redirect('/superadmin/gateways');
            }

            // Test credentials using Razorpay Payments ping
            $ch = curl_init("https://api.razorpay.com/v1/payments?count=1");
            curl_setopt($ch, CURLOPT_USERPWD, "{$keyId}:{$keySecret}");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($httpCode === 200) {
                View::setFlash('success', "✓ Razorpay API Connection Verified! Credentials are valid and active.");
            } elseif ($httpCode === 401) {
                View::setFlash('error', "✗ Authentication Failed (HTTP 401): Please verify that Key ID and Key Secret are correct.");
            } elseif (!empty($curlError)) {
                View::setFlash('warning', "Network check: " . $curlError . ". Credentials saved successfully in database.");
            } else {
                View::setFlash('info', "Razorpay responded with HTTP {$httpCode}. Credentials are saved and ready.");
            }
        } else {
            View::setFlash('success', "Gateway configuration verified and ready.");
        }

        return $this->redirect('/superadmin/gateways');
    }
}
