<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use App\Core\Logger;
use App\Core\View;

class SmtpEmailService implements EmailProviderInterface
{
    protected string $host;
    protected int $port;
    protected string $username;
    protected string $password;
    protected string $encryption;
    protected string $fromAddress;
    protected string $fromName;

    public function __construct()
    {
        $this->host        = (string)Env::get('MAIL_HOST', 'smtp.mailtrap.io');
        $this->port        = (int)Env::get('MAIL_PORT', 587);
        $this->username    = (string)Env::get('MAIL_USERNAME', '');
        $this->password    = (string)Env::get('MAIL_PASSWORD', '');
        $this->encryption  = strtolower((string)Env::get('MAIL_ENCRYPTION', 'tls'));
        $this->fromAddress = (string)Env::get('MAIL_FROM_ADDRESS', 'contact@refixel.com');
        $this->fromName    = (string)Env::get('MAIL_FROM_NAME', 'REFIXEL Home Services');
    }

    protected static bool $outboundSendingSuspended = false;

    public function send(string $to, string $subject, string $htmlBody, array $headers = []): bool
    {
        $to = trim($to);
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            Logger::error("Invalid recipient email skipped: [{$to}]");
            return false;
        }

        // Mock mailer for testing / offline
        if (Env::get('MAIL_MAILER') === 'mock' || Env::get('APP_ENV') === 'test') {
            Logger::info("Mock Email logged for {$to} [Subject: {$subject}]");
            return true;
        }

        // Brevo REST API delivery (Fast, highly reliable, bypasses local SMTP port blocks)
        $brevoApiKey = (string)Env::get('BREVO_API_KEY', '');
        if (!empty($brevoApiKey) && (Env::get('MAIL_MAILER') === 'brevo' || str_starts_with($brevoApiKey, 'xkeysib-'))) {
            $brevoOk = $this->sendViaBrevoApi($to, $subject, $htmlBody, $headers, $brevoApiKey);
            if ($brevoOk) {
                return true;
            }
            Logger::warning("Brevo API delivery failed for {$to}, attempting SMTP fallback...");
        }

        // Attempt SMTP socket delivery if credentials are provided or mailer is smtp
        if (!empty($this->username) && !empty($this->password) || Env::get('MAIL_MAILER') === 'smtp') {
            $smtpOk = $this->sendViaSmtp($to, $subject, $htmlBody, $headers);
            if ($smtpOk) {
                self::$outboundSendingSuspended = false;
                return true;
            }

            if (self::$outboundSendingSuspended) {
                // Do not attempt PHP mail() on local/Windows if Hostinger explicitly rejected outbound sending
                return false;
            }

            Logger::warning("SMTP socket delivery failed for {$to}, falling back to PHP mail().");
        }

        // Fallback to standard PHP mail()
        return $this->sendViaPhpMail($to, $subject, $htmlBody, $headers);
    }

    protected function sendViaBrevoApi(string $to, string $subject, string $htmlBody, array $headers = [], string $apiKey = ''): bool
    {
        $apiKey = !empty($apiKey) ? $apiKey : (string)Env::get('BREVO_API_KEY', '');
        if (empty($apiKey)) {
            Logger::error("Brevo API key is not configured.");
            return false;
        }

        $payload = [
            'sender' => [
                'name'  => $this->fromName,
                'email' => $this->fromAddress,
            ],
            'to' => [
                [
                    'email' => $to,
                ],
            ],
            'subject'     => $subject,
            'htmlContent' => $htmlBody,
        ];

        if (!empty($headers['Reply-To'])) {
            $payload['replyTo'] = ['email' => $headers['Reply-To']];
        }

        try {
            $ch = curl_init('https://api.brevo.com/v3/smtp/email');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => [
                    'api-key: ' . $apiKey,
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_TIMEOUT        => 12,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            if ($httpCode >= 200 && $httpCode < 300) {
                Logger::info("Brevo API email dispatched successfully to {$to} [Subject: {$subject}]");
                return true;
            }

            Logger::error("Brevo API error (HTTP {$httpCode}) to {$to}: {$response}" . ($curlErr ? " (curl: {$curlErr})" : ""));
            return false;
        } catch (\Throwable $e) {
            Logger::error("Brevo API exception for {$to}: " . $e->getMessage());
            return false;
        }
    }

    protected function sendViaPhpMail(string $to, string $subject, string $htmlBody, array $headers = []): bool
    {
        $defaultHeaders = [
            'MIME-Version' => '1.0',
            'Content-type' => 'text/html; charset=UTF-8',
            'From'         => "{$this->fromName} <{$this->fromAddress}>",
            'Reply-To'     => $this->fromAddress,
            'X-Mailer'     => 'REFIXEL-Mailer/2.0',
        ];

        $allHeaders = array_merge($defaultHeaders, $headers);
        $headerStr = '';
        foreach ($allHeaders as $k => $v) {
            $headerStr .= "{$k}: {$v}\r\n";
        }

        try {
            $fromEnvelope = "-f" . escapeshellarg($this->fromAddress);
            $ok = @mail($to, $subject, $htmlBody, $headerStr, $fromEnvelope);
            if (!$ok) {
                // Fallback without 5th parameter if server environment restricts it
                $ok = @mail($to, $subject, $htmlBody, $headerStr);
            }
            if ($ok) {
                Logger::info("Email dispatched via PHP mail() to {$to} [Subject: {$subject}]");
            } else {
                Logger::warning("PHP mail() returned false to {$to}");
            }
            return $ok;
        } catch (\Throwable $e) {
            Logger::error("PHP mail() error to {$to}: " . $e->getMessage());
            return false;
        }
    }

    protected function sendViaSmtp(string $to, string $subject, string $htmlBody, array $headers = []): bool
    {
        $timeout = 6;
        $hostPrefix = ($this->encryption === 'ssl' || $this->port === 465) ? 'ssl://' : '';
        $context = stream_context_create([
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ]
        ]);
        $socket = @stream_socket_client(
            $hostPrefix . $this->host . ':' . $this->port,
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$socket) {
            Logger::warning("SMTP connection error ({$errno}): {$errstr}");
            return false;
        }

        stream_set_timeout($socket, $timeout);

        try {
            $this->readSmtpResponse($socket, 220);

            // Send EHLO
            $this->writeSmtpCommand($socket, "EHLO " . gethostname(), 250);

            // Handle STARTTLS
            if (($this->encryption === 'tls' || $this->port === 587) && $hostPrefix === '') {
                $this->writeSmtpCommand($socket, "STARTTLS", 220);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new \RuntimeException("STARTTLS crypto handshake failed");
                }
                $this->writeSmtpCommand($socket, "EHLO " . gethostname(), 250);
            }

            // Authenticate if credentials exist
            if (!empty($this->username) && !empty($this->password)) {
                $this->writeSmtpCommand($socket, "AUTH LOGIN", 334);
                $this->writeSmtpCommand($socket, base64_encode($this->username), 334);
                $this->writeSmtpCommand($socket, base64_encode($this->password), 235);
            }

            // Mail From & Rcpt To
            $this->writeSmtpCommand($socket, "MAIL FROM:<{$this->fromAddress}>", 250);
            $this->writeSmtpCommand($socket, "RCPT TO:<{$to}>", 250);

            // Data
            $this->writeSmtpCommand($socket, "DATA", 354);

            $mimeHeaders = [
                'Date'         => date('r'),
                'To'           => $to,
                'From'         => "{$this->fromName} <{$this->fromAddress}>",
                'Reply-To'     => $this->fromAddress,
                'Subject'      => '=?UTF-8?B?' . base64_encode($subject) . '?=',
                'MIME-Version' => '1.0',
                'Content-Type' => 'text/html; charset=UTF-8',
                'X-Mailer'     => 'REFIXEL-Mailer/2.0',
            ];
            $mergedHeaders = array_merge($mimeHeaders, $headers);

            $payload = "";
            foreach ($mergedHeaders as $k => $v) {
                $payload .= "{$k}: {$v}\r\n";
            }
            $payload .= "\r\n" . $htmlBody . "\r\n.\r\n";

            fwrite($socket, $payload);
            $this->readSmtpResponse($socket, 250);

            $this->writeSmtpCommand($socket, "QUIT", 221);
            fclose($socket);

            Logger::info("SMTP email successfully sent to {$to} [Subject: {$subject}]");
            return true;
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'Outbound sending is disabled')) {
                self::$outboundSendingSuspended = true;
                Logger::error("CRITICAL HOSTINGER ALERT: Outbound sending is disabled for '{$this->fromAddress}'. Please log in to Hostinger hPanel -> Emails -> {$this->fromAddress} -> Settings -> Toggle OFF 'Suspend Outbound sending'.");
            } else {
                Logger::error("SMTP socket error to {$to}: " . $e->getMessage());
            }
            @fclose($socket);
            return false;
        }
    }

    protected function readSmtpResponse($socket, int $expectedCode): string
    {
        $response = "";
        while (!feof($socket)) {
            $line = fgets($socket, 512);
            if ($line === false) {
                break;
            }
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        $code = (int)substr($response, 0, 3);
        if ($code !== $expectedCode) {
            throw new \RuntimeException("SMTP error expected {$expectedCode}, got {$code}: {$response}");
        }
        return $response;
    }

    protected function writeSmtpCommand($socket, string $command, int $expectedCode): string
    {
        fwrite($socket, $command . "\r\n");
        return $this->readSmtpResponse($socket, $expectedCode);
    }

    public function sendTemplate(string $to, string $template, array $data = []): bool
    {
        $subject = match ($template) {
            'customer_welcome_booking'    => 'Booking Confirmed #' . ($data['booking_no'] ?? '') . ' & Your REFIXEL Account Details',
            'customer_enquiry_confirmed'  => '✅ Service Enquiry Received #' . ($data['booking_no'] ?? '') . ' & Your REFIXEL Account Details',
            'booking_confirmation'        => 'Your REFIXEL Booking is Confirmed - #' . ($data['booking_no'] ?? ''),
            'admin_new_booking'           => '🚨 New Booking Received: #' . ($data['booking_no'] ?? '') . ' (' . ($data['service_name'] ?? 'Home Service') . ')',
            'staff_assigned'              => '🔧 New Job Assigned: #' . ($data['booking_no'] ?? '') . ' - ' . ($data['service_name'] ?? 'Service'),
            'technician_assigned_customer'=> '🔧 Technician Assigned: Your Service Visit is Scheduled - Booking #' . ($data['booking_no'] ?? ''),
            'technician_accepted_customer'=> '✅ Technician Confirmed: Your Service is Scheduled - #' . ($data['booking_no'] ?? ''),
            'technician_on_the_way'       => '🚀 Your Technician is On The Way! - Booking #' . ($data['booking_no'] ?? ''),
            'technician_accepted_admin'   => '✅ Technician Accepted Job: #' . ($data['booking_no'] ?? '') . ' - ' . ($data['staff_name'] ?? 'Technician'),
            'invoice_prepaid_work_due'    => '📄 Payment Receipt: Work Scheduled - Booking #' . ($data['booking_no'] ?? ''),
            'invoice_final_completed'     => '🧾 Final GST Tax Invoice: Service Completed - Booking #' . ($data['booking_no'] ?? ''),
            'status_updated'              => 'Update on your Booking #' . ($data['booking_no'] ?? '') . ' - Status: ' . ($data['status'] ?? ''),
            'job_completed'               => 'Service Completed! Booking #' . ($data['booking_no'] ?? ''),
            'invoice_created'             => 'GST Tax Invoice Ready - ' . ($data['invoice_no'] ?? ''),
            'password_reset'              => 'Reset your REFIXEL Password',
            'admin_alert'                 => 'REFIXEL Platform Alert: ' . ($data['title'] ?? 'Attention Required'),
            'booking_reminder'            => 'Upcoming Service Visit Reminder - #' . ($data['booking_no'] ?? ''),
            'partner_application_received'=> 'Technician Application Received - Under Review | REFIXEL Partner Network',
            'partner_approved'            => '🎉 Congratulations! Your REFIXEL Partner Profile is Approved',
            'partner_rejected'            => 'Update on Your REFIXEL Partner Application',
            'partner_welcome'             => 'Welcome to REFIXEL Partner Network - Your Technician Portal Access',
            'admin_new_partner'           => '👷 New Service Partner Registered: ' . ($data['name'] ?? 'Technician') . ' (' . ($data['trade'] ?? 'Trade') . ')',
            'admin_welcome_credentials'   => '🔐 Welcome to REFIXEL - Your Admin Account Login Credentials',
            'admin_password_reset'        => '🔑 REFIXEL Admin Security - Your Account Password Has Been Updated',
            'enquiry_email_otp'           => '🔐 ' . ($data['otp'] ?? '') . ' is your REFIXEL Enquiry Verification Code',
            default                       => 'Notification from REFIXEL',
        };

        $htmlBody = $this->buildHtmlTemplate($template, $data);
        return $this->send($to, $subject, $htmlBody);
    }

    protected function buildHtmlTemplate(string $template, array $data): string
    {
        $loginUrl = !empty($data['login_url']) ? $data['login_url'] : View::fullUrl('/login');
        $adminUrl = !empty($data['admin_url']) ? $data['admin_url'] : View::fullUrl('/admin');
        $staffUrl = !empty($data['staff_job_url']) ? $data['staff_job_url'] : View::fullUrl('/staff/jobs');
        $bookingUrl = !empty($data['booking_url']) ? $data['booking_url'] : View::fullUrl('/account/bookings');
        $invoiceUrl = !empty($data['invoice_url']) ? $data['invoice_url'] : View::fullUrl('/account/invoices');

        $body = "<!DOCTYPE html><html><head><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'>";
        $body .= "<style type='text/css'>
        /* Mobile Responsive Overrides */
        @media only screen and (max-width: 600px) {
            body { padding: 8px 4px !important; }
            .email-container { max-width: 100% !important; width: 100% !important; border-radius: 8px !important; margin: 0 !important; }
            .email-header { padding: 18px 12px !important; }
            .email-body { padding: 20px 14px !important; }
            .email-footer { padding: 16px 12px !important; }
            .email-btn { display: block !important; width: 100% !important; box-sizing: border-box !important; text-align: center !important; padding: 14px 16px !important; }
            .email-table td { font-size: 13px !important; padding: 7px 6px !important; }
            .email-credentials { padding: 14px 12px !important; }
            .email-title { font-size: 20px !important; }
            .email-otp-box { font-size: 28px !important; letter-spacing: 5px !important; padding: 12px 18px !important; }
        }
        </style>";
        $body .= "</head><body style='margin:0; padding:20px; background:#f4f6f9; font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica, Arial, sans-serif; color: #1e293b;'>";
        $body .= "<div class='email-container' style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 16px rgba(0,0,0,0.06);'>";
        
        // Brand Header
        $body .= "<div class='email-header' style='background: #0a1c33; padding: 24px 20px; text-align: center; border-bottom: 3px solid #f25b29;'>";
        $body .= "<h1 style='margin: 0; color: #ffffff; font-size: 26px; font-weight: 800; letter-spacing: 0.5px;'>REFIXEL<span style='color: #f25b29;'>.</span></h1>";
        $body .= "<p style='margin: 4px 0 0; color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600;'>Mechanized Home & Maintenance Services</p>";
        $body .= "</div>";

        $body .= "<div class='email-body' style='padding: 28px 24px;'>";

        switch ($template) {
            case 'admin_welcome_credentials':
                $roleBadge = ($data['role'] ?? '') === 'super_admin' ? 'Super Administrator' : 'Operations Administrator';
                $roleBadgeBg = ($data['role'] ?? '') === 'super_admin' ? '#fdf2f8' : '#e0f2fe';
                $roleBadgeColor = ($data['role'] ?? '') === 'super_admin' ? '#9d174d' : '#0369a1';
                $adminName = htmlspecialchars($data['name'] ?? 'Admin');
                $adminEmail = htmlspecialchars($data['email'] ?? '');
                $adminPhone = htmlspecialchars($data['phone'] ?? '');
                $adminPassword = htmlspecialchars($data['password'] ?? '');

                $body .= "<div style='text-align: center; margin-bottom: 22px;'>";
                $body .= "<div style='display: inline-block; width: 64px; height: 64px; line-height: 64px; border-radius: 50%; background: #eff6ff; color: #2563eb; font-size: 28px; margin-bottom: 12px; box-shadow: 0 4px 12px rgba(37,99,235,0.15);'><i style='font-style: normal;'>🛡️</i></div><br>";
                $body .= "<span style='background: {$roleBadgeBg}; color: {$roleBadgeColor}; font-weight: 700; font-size: 12px; padding: 5px 14px; border-radius: 20px; display: inline-block; text-transform: uppercase; letter-spacing: 0.5px;'>{$roleBadge}</span>";
                $body .= "<h2 style='color: #0a1c33; font-size: 22px; margin: 12px 0 6px;'>Welcome to REFIXEL, " . $adminName . "!</h2>";
                $body .= "<p style='color: #64748b; font-size: 14px; margin: 0;'>An administrative account has been created for you on the REFIXEL Management Platform.</p>";
                $body .= "</div>";

                // Account Credentials Box
                $body .= "<div style='background: #0a1c33; color: #ffffff; border-radius: 12px; padding: 22px; margin-bottom: 24px; border-left: 5px solid #f25b29; box-shadow: 0 4px 16px rgba(10,28,51,0.15);'>";
                $body .= "<h3 style='margin: 0 0 12px; color: #ffffff; font-size: 16px;'><span style='color: #f25b29;'>🔑</span> Your Platform Login Credentials</h3>";
                $body .= "<p style='margin: 0 0 14px; color: #cbd5e1; font-size: 13px;'>Use the credentials below to log into the administrative management portal:</p>";
                $body .= "<div style='background: rgba(255,255,255,0.08); border-radius: 8px; padding: 14px; font-size: 14px;'>";
                if (!empty($adminEmail)) {
                    $body .= "<div style='margin-bottom: 8px;'><strong>Login Email:</strong> <span style='color: #38bdf8; font-weight: 600; font-family: monospace;'>" . $adminEmail . "</span></div>";
                }
                if (!empty($adminPhone)) {
                    $body .= "<div style='margin-bottom: 8px;'><strong>Registered Phone:</strong> <span style='color: #e2e8f0; font-weight: 500; font-family: monospace;'>" . $adminPhone . "</span></div>";
                }
                $body .= "<div style='margin-bottom: 8px;'><strong>Assigned Role:</strong> <span style='color: #fcd34d; font-weight: 600;'>" . htmlspecialchars($roleBadge) . "</span></div>";
                $body .= "<div><strong>Temporary Password:</strong> <span style='background: #f25b29; color: #ffffff; padding: 3px 10px; border-radius: 4px; font-family: Monaco, Menlo, Consolas, monospace; font-weight: 700; font-size: 15px; letter-spacing: 0.5px;'>" . $adminPassword . "</span></div>";
                $body .= "</div>";
                $body .= "<p style='margin: 12px 0 0; color: #94a3b8; font-size: 12px;'>⚠️ For security, please keep these credentials safe and update your password upon your first login.</p>";
                $body .= "</div>";

                // CTA Button
                $body .= "<div style='text-align: center; margin: 26px 0;'>";
                $body .= "<a href='" . htmlspecialchars($loginUrl) . "' style='background: #f25b29; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 14px 34px; border-radius: 8px; display: inline-block; box-shadow: 0 4px 14px rgba(242,91,41,0.3);'>Sign In to Admin Portal &rarr;</a>";
                $body .= "<p style='margin: 8px 0 0; color: #94a3b8; font-size: 12px;'>Portal URL: <a href='" . htmlspecialchars($loginUrl) . "' style='color: #0284c7;'>" . htmlspecialchars($loginUrl) . "</a></p>";
                $body .= "</div>";
                break;

            case 'admin_password_reset':
                $roleBadge = ($data['role'] ?? '') === 'super_admin' ? 'Super Administrator' : 'Operations Administrator';
                $roleBadgeBg = ($data['role'] ?? '') === 'super_admin' ? '#fdf2f8' : '#e0f2fe';
                $roleBadgeColor = ($data['role'] ?? '') === 'super_admin' ? '#9d174d' : '#0369a1';
                $adminName = htmlspecialchars($data['name'] ?? 'Admin');
                $adminEmail = htmlspecialchars($data['email'] ?? '');
                $adminPhone = htmlspecialchars($data['phone'] ?? '');
                $adminPassword = htmlspecialchars($data['password'] ?? '');

                $body .= "<div style='text-align: center; margin-bottom: 22px;'>";
                $body .= "<div style='display: inline-block; width: 64px; height: 64px; line-height: 64px; border-radius: 50%; background: #fef3c7; color: #d97706; font-size: 28px; margin-bottom: 12px; box-shadow: 0 4px 12px rgba(217,119,6,0.15);'><i style='font-style: normal;'>🔑</i></div><br>";
                $body .= "<span style='background: {$roleBadgeBg}; color: {$roleBadgeColor}; font-weight: 700; font-size: 12px; padding: 5px 14px; border-radius: 20px; display: inline-block; text-transform: uppercase; letter-spacing: 0.5px;'>{$roleBadge}</span>";
                $body .= "<h2 style='color: #0a1c33; font-size: 22px; margin: 12px 0 6px;'>Password Updated, " . $adminName . "!</h2>";
                $body .= "<p style='color: #64748b; font-size: 14px; margin: 0;'>Your REFIXEL administrative access password has been updated by Super Admin Governance.</p>";
                $body .= "</div>";

                // Account Credentials Box
                $body .= "<div style='background: #0a1c33; color: #ffffff; border-radius: 12px; padding: 22px; margin-bottom: 24px; border-left: 5px solid #f25b29; box-shadow: 0 4px 16px rgba(10,28,51,0.15);'>";
                $body .= "<h3 style='margin: 0 0 12px; color: #ffffff; font-size: 16px;'><span style='color: #f25b29;'>🔑</span> Updated Sign-In Credentials</h3>";
                $body .= "<p style='margin: 0 0 14px; color: #cbd5e1; font-size: 13px;'>Your updated sign-in credentials are shown below:</p>";
                $body .= "<div style='background: rgba(255,255,255,0.08); border-radius: 8px; padding: 14px; font-size: 14px;'>";
                if (!empty($adminEmail)) {
                    $body .= "<div style='margin-bottom: 8px;'><strong>Login Email:</strong> <span style='color: #38bdf8; font-weight: 600; font-family: monospace;'>" . $adminEmail . "</span></div>";
                }
                if (!empty($adminPhone)) {
                    $body .= "<div style='margin-bottom: 8px;'><strong>Registered Phone:</strong> <span style='color: #e2e8f0; font-weight: 500; font-family: monospace;'>" . $adminPhone . "</span></div>";
                }
                $body .= "<div style='margin-bottom: 8px;'><strong>Assigned Role:</strong> <span style='color: #fcd34d; font-weight: 600;'>" . htmlspecialchars($roleBadge) . "</span></div>";
                $body .= "<div><strong>New Password:</strong> <span style='background: #f25b29; color: #ffffff; padding: 3px 10px; border-radius: 4px; font-family: Monaco, Menlo, Consolas, monospace; font-weight: 700; font-size: 15px; letter-spacing: 0.5px;'>" . $adminPassword . "</span></div>";
                $body .= "</div>";
                $body .= "<p style='margin: 12px 0 0; color: #94a3b8; font-size: 12px;'>⚠️ If you did not request this update, contact your Super Administrator immediately.</p>";
                $body .= "</div>";

                // CTA Button
                $body .= "<div style='text-align: center; margin: 26px 0;'>";
                $body .= "<a href='" . htmlspecialchars($loginUrl) . "' style='background: #f25b29; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 14px 34px; border-radius: 8px; display: inline-block; box-shadow: 0 4px 14px rgba(242,91,41,0.3);'>Sign In to Admin Portal &rarr;</a>";
                $body .= "<p style='margin: 8px 0 0; color: #94a3b8; font-size: 12px;'>Portal URL: <a href='" . htmlspecialchars($loginUrl) . "' style='color: #0284c7;'>" . htmlspecialchars($loginUrl) . "</a></p>";
                $body .= "</div>";
                break;

            case 'customer_enquiry_confirmed':
                $body .= "<div style='text-align: center; margin-bottom: 22px;'>";
                $body .= "<span style='background: #dcfce7; color: #15803d; font-weight: 700; font-size: 13px; padding: 6px 14px; border-radius: 20px; display: inline-block;'>Enquiry Confirmed</span>";
                $body .= "<h2 class='email-title' style='color: #0a1c33; font-size: 22px; margin: 12px 0 6px;'>Service Enquiry Confirmed, " . htmlspecialchars($data['customer_name'] ?? 'Valued Customer') . "!</h2>";
                $body .= "<p style='color: #64748b; font-size: 14px; margin: 0;'>Your verified service enquiry has been received and assigned a priority tracking ID.</p>";
                $body .= "</div>";

                // Summary Card
                $body .= "<div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 20px;'>";
                $body .= "<div style='display: flex; justify-content: space-between; margin-bottom: 8px;'>";
                $body .= "<strong style='color: #0a1c33;'>Enquiry / Booking ID:</strong> <span style='font-family: monospace; font-weight: 700; color: #f25b29;'>#" . htmlspecialchars($data['booking_no'] ?? '') . "</span>";
                $body .= "</div>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #0a1c33;'>Service Requested:</strong> " . htmlspecialchars($data['service_name'] ?? 'Home Service') . "</div>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #0a1c33;'>Preferred Slot:</strong> " . htmlspecialchars($data['preferred_date'] ?? date('Y-m-d')) . " • " . htmlspecialchars($data['preferred_time'] ?? 'Immediate / ASAP') . "</div>";
                $body .= "<div><strong style='color: #0a1c33;'>Service Area:</strong> " . htmlspecialchars($data['address'] ?? 'Uttarakhand Service Area') . "</div>";
                $body .= "</div>";

                // Account Credentials Box
                if (!empty($data['generated_password'])) {
                    $body .= "<div class='email-credentials' style='background: #0a1c33; color: #ffffff; border-radius: 10px; padding: 20px; margin-bottom: 24px; border-left: 5px solid #f25b29;'>";
                    $body .= "<h3 style='margin: 0 0 10px; color: #ffffff; font-size: 16px;'><span style='color: #f25b29;'>🔑</span> Your Customer Account Login Credentials</h3>";
                    $body .= "<p style='margin: 0 0 12px; color: #cbd5e1; font-size: 13px;'>Your REFIXEL customer portal account is active so you can track technician assignment, chat with support, and view GST invoices:</p>";
                    $body .= "<div style='background: rgba(255,255,255,0.08); border-radius: 8px; padding: 12px; font-size: 14px;'>";
                    $body .= "<div style='margin-bottom: 6px;'><strong>Login Phone / ID:</strong> <span style='color: #38bdf8; font-weight: 600; font-family: monospace;'>" . htmlspecialchars($data['identifier'] ?? ($data['phone'] ?? $data['email'] ?? '')) . "</span></div>";
                    if (!empty($data['email'])) {
                        $body .= "<div style='margin-bottom: 6px;'><strong>Registered Email:</strong> <span style='color: #e2e8f0; font-weight: 500;'>" . htmlspecialchars($data['email']) . "</span></div>";
                    }
                    $body .= "<div><strong>Temporary Password:</strong> <span style='background: #f25b29; color: #ffffff; padding: 2px 8px; border-radius: 4px; font-family: monospace; font-weight: 700; font-size: 15px;'>" . htmlspecialchars($data['generated_password']) . "</span></div>";
                    $body .= "</div>";
                    $body .= "<p style='margin: 10px 0 0; color: #94a3b8; font-size: 12px;'>You can change this password at any time in your profile.</p>";
                    $body .= "</div>";
                }

                // CTA Button
                $body .= "<div style='text-align: center; margin: 26px 0;'>";
                $body .= "<a href='" . htmlspecialchars($loginUrl) . "' class='email-btn' style='background: #f25b29; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 13px 32px; border-radius: 8px; display: inline-block; box-shadow: 0 4px 12px rgba(242,91,41,0.3);'>Log In to Customer Portal &rarr;</a>";
                $body .= "</div>";
                break;

            case 'customer_welcome_booking':
                $body .= "<div style='text-align: center; margin-bottom: 22px;'>";
                $body .= "<span style='background: #dcfce7; color: #15803d; font-weight: 700; font-size: 13px; padding: 6px 14px; border-radius: 20px; display: inline-block;'>Booking Confirmed</span>";
                $body .= "<h2 style='color: #0a1c33; font-size: 20px; margin: 12px 0 6px;'>Welcome to REFIXEL, " . htmlspecialchars($data['customer_name'] ?? 'Valued Customer') . "!</h2>";
                $body .= "<p style='color: #64748b; font-size: 14px; margin: 0;'>Your booking has been received and your customer portal account is ready.</p>";
                $body .= "</div>";

                // Booking Summary Card
                $body .= "<div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 20px;'>";
                $body .= "<div style='display: flex; justify-content: space-between; margin-bottom: 8px;'>";
                $body .= "<strong style='color: #0a1c33;'>Booking ID:</strong> <span style='font-family: monospace; font-weight: 700; color: #f25b29;'>#" . htmlspecialchars($data['booking_no'] ?? '') . "</span>";
                $body .= "</div>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #0a1c33;'>Service:</strong> " . htmlspecialchars($data['service_name'] ?? 'Home Service') . "</div>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #0a1c33;'>Scheduled Slot:</strong> " . htmlspecialchars($data['preferred_date'] ?? date('Y-m-d')) . " • " . htmlspecialchars($data['preferred_time'] ?? 'Flexible') . "</div>";
                $body .= "<div><strong style='color: #0a1c33;'>Service Location:</strong> " . htmlspecialchars($data['address'] ?? 'Service Address') . "</div>";
                $body .= "</div>";

                // Account Credentials Box
                if (!empty($data['generated_password'])) {
                    $body .= "<div style='background: #0a1c33; color: #ffffff; border-radius: 10px; padding: 20px; margin-bottom: 24px; border-left: 5px solid #f25b29;'>";
                    $body .= "<h3 style='margin: 0 0 10px; color: #ffffff; font-size: 16px;'><span style='color: #f25b29;'>🔑</span> Your Account Login Credentials</h3>";
                    $body .= "<p style='margin: 0 0 12px; color: #cbd5e1; font-size: 13px;'>We generated an account so you can track your technician in real-time, view GST invoices, and manage bookings anytime:</p>";
                    $body .= "<div style='background: rgba(255,255,255,0.08); border-radius: 8px; padding: 12px; font-size: 14px;'>";
                    $body .= "<div style='margin-bottom: 6px;'><strong>Login Phone / Email:</strong> <span style='color: #38bdf8; font-weight: 600;'>" . htmlspecialchars($data['identifier'] ?? ($data['phone'] ?? $data['email'] ?? '')) . "</span></div>";
                    $body .= "<div><strong>Password:</strong> <span style='background: #f25b29; color: #ffffff; padding: 2px 8px; border-radius: 4px; font-family: monospace; font-weight: 700; font-size: 15px;'>" . htmlspecialchars($data['generated_password']) . "</span></div>";
                    $body .= "</div>";
                    $body .= "<p style='margin: 10px 0 0; color: #94a3b8; font-size: 12px;'>You can change this password at any time in your profile.</p>";
                    $body .= "</div>";
                }

                // CTA Button
                $body .= "<div style='text-align: center; margin: 26px 0;'>";
                $body .= "<a href='" . htmlspecialchars($loginUrl) . "' style='background: #f25b29; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 13px 32px; border-radius: 8px; display: inline-block; box-shadow: 0 4px 12px rgba(242,91,41,0.3);'>Login to Your Account &rarr;</a>";
                $body .= "</div>";
                break;

            case 'booking_confirmation':
                $body .= "<div style='text-align: center; margin-bottom: 20px;'>";
                $body .= "<span style='background: #dcfce7; color: #15803d; font-weight: 700; font-size: 13px; padding: 6px 14px; border-radius: 20px; display: inline-block;'>Booking Confirmed</span>";
                $body .= "<h2 style='color: #0a1c33; font-size: 20px; margin: 12px 0 6px;'>Booking #" . htmlspecialchars($data['booking_no'] ?? '') . " Confirmed!</h2>";
                $body .= "<p style='color: #64748b; font-size: 14px; margin: 0;'>Dear " . htmlspecialchars($data['customer_name'] ?? 'Customer') . ", thank you for booking with REFIXEL.</p>";
                $body .= "</div>";

                $body .= "<div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 22px; font-size: 14px;'>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #0a1c33;'>Service:</strong> " . htmlspecialchars($data['service_name'] ?? 'Home Service') . "</div>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #0a1c33;'>Date & Time:</strong> " . htmlspecialchars($data['preferred_date'] ?? '') . " (" . htmlspecialchars($data['preferred_time'] ?? '') . ")</div>";
                $body .= "<div><strong style='color: #0a1c33;'>Address:</strong> " . htmlspecialchars($data['address'] ?? '') . "</div>";
                $body .= "</div>";

                $body .= "<div style='text-align: center; margin: 24px 0;'>";
                $body .= "<a href='" . htmlspecialchars($bookingUrl) . "' style='background: #0a1c33; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14px; padding: 12px 28px; border-radius: 8px; display: inline-block;'>Track Booking Status &rarr;</a>";
                $body .= "</div>";
                break;

            case 'admin_new_booking':
                $body .= "<div style='margin-bottom: 18px;'>";
                $body .= "<span style='background: #fee2e2; color: #b91c1c; font-weight: 700; font-size: 12px; padding: 4px 10px; border-radius: 6px; text-transform: uppercase;'>New Booking Alert</span>";
                $body .= "<h2 style='color: #0a1c33; font-size: 20px; margin: 10px 0 6px;'>New Booking Received #" . htmlspecialchars($data['booking_no'] ?? '') . "</h2>";
                $body .= "<p style='color: #64748b; font-size: 14px; margin: 0;'>A customer just requested a service on the REFIXEL platform.</p>";
                $body .= "</div>";

                $body .= "<table style='width: 100%; border-collapse: collapse; font-size: 14px; margin-bottom: 24px;'>";
                $body .= "<tr><td style='padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; width: 38%; font-weight: 600; color: #475569;'>Service</td><td style='padding: 8px 12px; border: 1px solid #e2e8f0; font-weight: 700; color: #0a1c33;'>" . htmlspecialchars($data['service_name'] ?? 'Home Service') . "</td></tr>";
                $body .= "<tr><td style='padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; font-weight: 600; color: #475569;'>Customer Name</td><td style='padding: 8px 12px; border: 1px solid #e2e8f0; color: #0a1c33;'>" . htmlspecialchars($data['customer_name'] ?? '') . "</td></tr>";
                $body .= "<tr><td style='padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; font-weight: 600; color: #475569;'>Phone</td><td style='padding: 8px 12px; border: 1px solid #e2e8f0;'><a href='tel:" . htmlspecialchars($data['phone'] ?? '') . "' style='color: #f25b29; font-weight: 600;'>" . htmlspecialchars($data['phone'] ?? '') . "</a></td></tr>";
                if (!empty($data['email'])) {
                    $body .= "<tr><td style='padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; font-weight: 600; color: #475569;'>Email</td><td style='padding: 8px 12px; border: 1px solid #e2e8f0;'><a href='mailto:" . htmlspecialchars($data['email']) . "' style='color: #0284c7;'>" . htmlspecialchars($data['email']) . "</a></td></tr>";
                }
                $body .= "<tr><td style='padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; font-weight: 600; color: #475569;'>Scheduled Slot</td><td style='padding: 8px 12px; border: 1px solid #e2e8f0; color: #0a1c33;'>" . htmlspecialchars($data['preferred_date'] ?? date('Y-m-d')) . " (" . htmlspecialchars($data['preferred_time'] ?? 'Flexible') . ")</td></tr>";
                $body .= "<tr><td style='padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; font-weight: 600; color: #475569;'>Location</td><td style='padding: 8px 12px; border: 1px solid #e2e8f0; color: #0a1c33;'>" . htmlspecialchars($data['address'] ?? ($data['city'] ?? '')) . "</td></tr>";
                if (!empty($data['message']) || !empty($data['issue_details'])) {
                    $body .= "<tr><td style='padding: 8px 12px; background: #f8fafc; border: 1px solid #e2e8f0; font-weight: 600; color: #475569;'>Notes / Message</td><td style='padding: 8px 12px; border: 1px solid #e2e8f0; color: #64748b;'>" . htmlspecialchars($data['message'] ?? $data['issue_details']) . "</td></tr>";
                }
                $body .= "</table>";

                $body .= "<div style='text-align: center; margin: 26px 0;'>";
                $body .= "<a href='" . htmlspecialchars($adminUrl) . "' style='background: #f25b29; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 13px 30px; border-radius: 8px; display: inline-block; box-shadow: 0 4px 12px rgba(242,91,41,0.25);'>Open in Admin Dashboard &rarr;</a>";
                $body .= "<p style='margin: 8px 0 0; color: #94a3b8; font-size: 12px;'>Opens directly if logged in, or redirects after admin login.</p>";
                $body .= "</div>";
                break;

            case 'staff_assigned':
                $body .= "<div style='margin-bottom: 18px;'>";
                $body .= "<span style='background: #e0f2fe; color: #0369a1; font-weight: 700; font-size: 12px; padding: 4px 10px; border-radius: 6px; text-transform: uppercase;'>New Job Assigned</span>";
                $body .= "<h2 style='color: #0a1c33; font-size: 20px; margin: 10px 0 6px;'>Hello " . htmlspecialchars($data['staff_name'] ?? 'Technician') . ", you have a new job!</h2>";
                $body .= "<p style='color: #64748b; font-size: 14px; margin: 0;'>A new booking has been assigned to you. Please review and accept in your portal:</p>";
                $body .= "</div>";

                $workerPctDisplay = !empty($data['worker_percent']) ? ' (' . (float)$data['worker_percent'] . '% share)' : '';
                $payoutDisplay = !empty($data['technician_payout']) ? '₹' . number_format((float)$data['technician_payout'], 0) : '₹850';

                $body .= "<div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; margin-bottom: 22px; font-size: 14px;'>";
                $body .= "<div style='display: flex; justify-content: space-between; margin-bottom: 8px;'><strong style='color: #0a1c33;'>Booking ID:</strong> <span style='font-family: monospace; font-weight: 700; color: #f25b29;'>#" . htmlspecialchars($data['booking_no'] ?? '') . "</span></div>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #0a1c33;'>Service:</strong> " . htmlspecialchars($data['service_name'] ?? 'Home Service') . "</div>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #0a1c33;'>Scheduled:</strong> " . htmlspecialchars($data['preferred_date'] ?? date('Y-m-d')) . " (" . htmlspecialchars($data['preferred_time'] ?? 'Flexible') . ")</div>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #0a1c33;'>Address:</strong> " . htmlspecialchars($data['address'] ?? '') . "</div>";
                $body .= "<div style='padding-top: 10px; border-top: 1px dashed #cbd5e1; margin-top: 10px;'><strong style='color: #15803d;'>Your Expected Payout" . $workerPctDisplay . ":</strong> <span style='font-size: 18px; font-weight: 800; color: #16a34a;'>" . $payoutDisplay . "</span></div>";
                $body .= "</div>";

                $body .= "<div style='text-align: center; margin: 24px 0;'>";
                $body .= "<a href='" . htmlspecialchars($staffUrl) . "' style='background: #10b981; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 13px 30px; border-radius: 8px; display: inline-block;'>View & Accept Job &rarr;</a>";
                $body .= "</div>";
                break;

            case 'technician_assigned_customer':
                $body .= "<div style='text-align: center; margin-bottom: 20px;'>";
                $body .= "<span style='background: #e0f2fe; color: #0284c7; font-weight: 700; font-size: 13px; padding: 6px 16px; border-radius: 20px; display: inline-block; letter-spacing: 0.3px;'>🔧 Verified Technician Assigned</span>";
                $body .= "<h2 style='color: #0a1c33; font-size: 22px; margin: 12px 0 6px;'>Technician Assigned to Your Service Request</h2>";
                $body .= "<p style='color: #64748b; font-size: 14px; margin: 0; line-height: 1.5;'>Dear " . htmlspecialchars($data['customer_name'] ?? 'Customer') . ", a certified REFIXEL service specialist has been assigned to your booking <strong>#" . htmlspecialchars($data['booking_no'] ?? '') . "</strong>.</p>";
                $body .= "</div>";

                $body .= "<div style='background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 20px; margin-bottom: 20px;'>";
                $body .= "<div style='display: flex; align-items: center; justify-content: space-between; border-bottom: 1px dashed #cbd5e1; padding-bottom: 12px; margin-bottom: 14px;'>";
                $body .= "<div>";
                $body .= "<div style='font-size: 11px; text-transform: uppercase; font-weight: 700; color: #f25b29; letter-spacing: 0.5px;'>Assigned Professional</div>";
                $body .= "<div style='font-size: 18px; font-weight: 800; color: #0f172a; margin-top: 2px;'>" . htmlspecialchars($data['staff_name'] ?? 'Certified Technician') . "</div>";
                $body .= "</div>";
                if (!empty($data['staff_phone'])) {
                    $body .= "<a href='tel:" . htmlspecialchars($data['staff_phone']) . "' style='background: #10b981; color: #ffffff; text-decoration: none; font-size: 13px; font-weight: 700; padding: 8px 14px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;'>📞 Call Technician</a>";
                }
                $body .= "</div>";

                $body .= "<table style='width: 100%; border-collapse: collapse; font-size: 13.5px;'>";
                $body .= "<tr><td style='padding: 6px 0; color: #64748b; width: 38%; font-weight: 600;'>Service Booked:</td><td style='padding: 6px 0; font-weight: 700; color: #0f172a;'>" . htmlspecialchars($data['service_name'] ?? 'Home Service') . "</td></tr>";
                if (!empty($data['staff_phone'])) {
                    $body .= "<tr><td style='padding: 6px 0; color: #64748b; font-weight: 600;'>Technician Mobile:</td><td style='padding: 6px 0; font-weight: 700;'><a href='tel:" . htmlspecialchars($data['staff_phone']) . "' style='color: #f25b29; text-decoration: none;'>" . htmlspecialchars($data['staff_phone']) . "</a></td></tr>";
                }
                $body .= "<tr><td style='padding: 6px 0; color: #64748b; font-weight: 600;'>Scheduled Visit:</td><td style='padding: 6px 0; font-weight: 700; color: #0f172a;'>" . htmlspecialchars($data['preferred_date'] ?? date('Y-m-d')) . " (" . htmlspecialchars($data['preferred_time'] ?? 'Flexible Slot') . ")</td></tr>";
                if (!empty($data['address'])) {
                    $body .= "<tr><td style='padding: 6px 0; color: #64748b; font-weight: 600;'>Service Address:</td><td style='padding: 6px 0; color: #334155; line-height: 1.4;'>" . htmlspecialchars($data['address']) . "</td></tr>";
                }
                $body .= "</table>";
                $body .= "</div>";

                $body .= "<div style='background: #f0fdf4; border-left: 4px solid #16a34a; padding: 14px 16px; border-radius: 6px; margin-bottom: 22px; font-size: 13px; color: #166534; line-height: 1.5;'>";
                $body .= "<strong>What Happens Next?</strong><br>";
                $body .= "&bull; Our verified professional will contact you on <strong>" . htmlspecialchars($data['customer_phone'] ?? 'your phone') . "</strong> prior to arrival to confirm timing.<br>";
                $body .= "&bull; All REFIXEL technicians carry specialized tools and mechanized equipment for top-quality service.<br>";
                $body .= "&bull; Pay safely via online invoice or cash only after the service is performed to your complete satisfaction.";
                $body .= "</div>";

                $body .= "<div style='text-align: center; margin: 24px 0;'>";
                $body .= "<a href='" . htmlspecialchars($bookingUrl) . "' style='background: #f25b29; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14px; padding: 13px 30px; border-radius: 8px; display: inline-block; box-shadow: 0 4px 14px rgba(242,91,41,0.25);'>View Booking Details & Track &rarr;</a>";
                $body .= "</div>";
                break;

            case 'technician_accepted_customer':
                $body .= "<div style='text-align: center; margin-bottom: 20px;'>";
                $body .= "<span style='background: #dcfce7; color: #15803d; font-weight: 700; font-size: 13px; padding: 6px 14px; border-radius: 20px; display: inline-block;'>Technician Assigned & Confirmed</span>";
                $body .= "<h2 style='color: #0a1c33; font-size: 20px; margin: 12px 0 6px;'>Your Technician is on the Way / Scheduled!</h2>";
                $body .= "<p style='color: #64748b; font-size: 14px; margin: 0;'>Dear " . htmlspecialchars($data['customer_name'] ?? 'Customer') . ", your service booking #" . htmlspecialchars($data['booking_no'] ?? '') . " has been accepted.</p>";
                $body .= "</div>";

                $body .= "<div style='background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 18px; margin-bottom: 22px; font-size: 14px;'>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #1e3a8a;'>Assigned Technician:</strong> <span style='font-weight: 700; color: #0a1c33;'>" . htmlspecialchars($data['staff_name'] ?? 'Assigned Technician') . "</span></div>";
                if (!empty($data['staff_phone'])) {
                    $body .= "<div style='margin-bottom: 8px;'><strong style='color: #1e3a8a;'>Technician Contact:</strong> <a href='tel:" . htmlspecialchars($data['staff_phone']) . "' style='color: #f25b29; font-weight: 700;'>" . htmlspecialchars($data['staff_phone']) . "</a></div>";
                }
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #1e3a8a;'>Service:</strong> " . htmlspecialchars($data['service_name'] ?? 'Home Service') . "</div>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #1e3a8a;'>Scheduled Slot:</strong> " . htmlspecialchars($data['preferred_date'] ?? date('Y-m-d')) . " • " . htmlspecialchars($data['preferred_time'] ?? 'Flexible') . "</div>";
                $body .= "<div><strong style='color: #1e3a8a;'>Location:</strong> " . htmlspecialchars($data['address'] ?? '') . "</div>";
                $body .= "</div>";

                $body .= "<div style='text-align: center; margin: 24px 0;'>";
                $body .= "<a href='" . htmlspecialchars($bookingUrl) . "' class='email-btn' style='background: #0a1c33; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14px; padding: 12px 28px; border-radius: 8px; display: inline-block;'>View Booking in Portal &rarr;</a>";
                $body .= "</div>";
                break;

            case 'technician_on_the_way':
                $body .= "<div style='text-align: center; margin-bottom: 20px;'>";
                $body .= "<span style='background: #e0f2fe; color: #0284c7; font-weight: 700; font-size: 13px; padding: 6px 16px; border-radius: 20px; display: inline-block; letter-spacing: 0.3px;'>🚀 Technician En Route</span>";
                $body .= "<h2 class='email-title' style='color: #0a1c33; font-size: 22px; margin: 12px 0 6px;'>Your Technician is On The Way!</h2>";
                $body .= "<p style='color: #64748b; font-size: 14px; margin: 0; line-height: 1.5;'>Dear " . htmlspecialchars($data['customer_name'] ?? 'Customer') . ", your assigned specialist is travelling to your location for Booking <strong>#" . htmlspecialchars($data['booking_no'] ?? '') . "</strong>.</p>";
                $body .= "</div>";

                $body .= "<div style='background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 20px; margin-bottom: 20px;'>";
                $body .= "<div style='display: flex; align-items: center; justify-content: space-between; border-bottom: 1px dashed #cbd5e1; padding-bottom: 12px; margin-bottom: 14px;'>";
                $body .= "<div>";
                $body .= "<div style='font-size: 11px; text-transform: uppercase; font-weight: 700; color: #f25b29; letter-spacing: 0.5px;'>Assigned Professional</div>";
                $body .= "<div style='font-size: 18px; font-weight: 800; color: #0f172a; margin-top: 2px;'>" . htmlspecialchars($data['staff_name'] ?? 'Technician') . "</div>";
                $body .= "</div>";
                if (!empty($data['staff_phone'])) {
                    $body .= "<a href='tel:" . htmlspecialchars($data['staff_phone']) . "' style='background: #10b981; color: #ffffff; text-decoration: none; font-size: 13px; font-weight: 700; padding: 8px 14px; border-radius: 8px; display: inline-flex; align-items: center; gap: 6px;'>📞 Call Now</a>";
                }
                $body .= "</div>";

                $body .= "<table class='email-table' style='width: 100%; border-collapse: collapse; font-size: 13.5px;'>";
                $body .= "<tr><td style='padding: 6px 0; color: #64748b; width: 38%; font-weight: 600;'>Service Booked:</td><td style='padding: 6px 0; font-weight: 700; color: #0f172a;'>" . htmlspecialchars($data['service_name'] ?? 'Home Service') . "</td></tr>";
                if (!empty($data['staff_phone'])) {
                    $body .= "<tr><td style='padding: 6px 0; color: #64748b; font-weight: 600;'>Technician Phone:</td><td style='padding: 6px 0; font-weight: 700;'><a href='tel:" . htmlspecialchars($data['staff_phone']) . "' style='color: #f25b29; text-decoration: none;'>" . htmlspecialchars($data['staff_phone']) . "</a></td></tr>";
                }
                $body .= "<tr><td style='padding: 6px 0; color: #64748b; font-weight: 600;'>Destination Address:</td><td style='padding: 6px 0; color: #334155; line-height: 1.4;'>" . htmlspecialchars($data['address'] ?? '') . "</td></tr>";
                $body .= "</table>";
                $body .= "</div>";

                $body .= "<div style='background: #fff7ed; border-left: 4px solid #f97316; padding: 14px 16px; border-radius: 6px; margin-bottom: 22px; font-size: 13.5px; color: #9a3412; line-height: 1.5;'>";
                $body .= "<strong>Arrival Window:</strong><br>";
                $body .= "&bull; Our technician expects to arrive within <strong>15 to 30 minutes</strong> depending on traffic conditions.<br>";
                $body .= "&bull; Please ensure someone is available at the premises and keep your contact mobile reachable.";
                $body .= "</div>";

                $body .= "<div style='text-align: center; margin: 24px 0;'>";
                $body .= "<a href='" . htmlspecialchars($bookingUrl) . "' class='email-btn' style='background: #f25b29; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14px; padding: 13px 30px; border-radius: 8px; display: inline-block; box-shadow: 0 4px 14px rgba(242,91,41,0.25);'>Track Service Live &rarr;</a>";
                $body .= "</div>";
                break;

            case 'technician_accepted_admin':
                $body .= "<div style='margin-bottom: 18px;'>";
                $body .= "<span style='background: #dcfce7; color: #15803d; font-weight: 700; font-size: 12px; padding: 4px 10px; border-radius: 6px; text-transform: uppercase;'>Technician Accepted</span>";
                $body .= "<h2 style='color: #0a1c33; font-size: 20px; margin: 10px 0 6px;'>Job Accepted for Booking #" . htmlspecialchars($data['booking_no'] ?? '') . "</h2>";
                $body .= "<p style='color: #64748b; font-size: 14px; margin: 0;'>Technician <strong>" . htmlspecialchars($data['staff_name'] ?? 'Staff') . "</strong> (" . htmlspecialchars($data['staff_phone'] ?? '') . ") has accepted the assignment.</p>";
                $body .= "</div>";

                $body .= "<div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 22px; font-size: 14px;'>";
                $body .= "<div style='margin-bottom: 8px;'><strong>Service:</strong> " . htmlspecialchars($data['service_name'] ?? 'Home Service') . "</div>";
                $body .= "<div style='margin-bottom: 8px;'><strong>Scheduled:</strong> " . htmlspecialchars($data['preferred_date'] ?? '') . " (" . htmlspecialchars($data['preferred_time'] ?? '') . ")</div>";
                $body .= "<div><strong>Customer:</strong> " . htmlspecialchars($data['customer_name'] ?? '') . " (" . htmlspecialchars($data['customer_phone'] ?? '') . ")</div>";
                $body .= "</div>";

                $body .= "<div style='text-align: center; margin: 24px 0;'>";
                $body .= "<a href='" . htmlspecialchars($adminUrl) . "' style='background: #f25b29; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14px; padding: 12px 28px; border-radius: 8px; display: inline-block;'>View in Admin &rarr;</a>";
                $body .= "</div>";
                break;

            case 'invoice_prepaid_work_due':
                $body .= "<div style='text-align: center; margin-bottom: 20px;'>";
                $body .= "<span style='background: #fef3c7; color: #92400e; font-weight: 700; font-size: 12px; padding: 5px 12px; border-radius: 20px; display: inline-block;'>Payment Received &bull; Work is Due</span>";
                $body .= "<h2 style='color: #0a1c33; font-size: 20px; margin: 12px 0 6px;'>Payment Receipt & GST Tax Invoice</h2>";
                $body .= "<p style='color: #64748b; font-size: 14px; margin: 0;'>Dear " . htmlspecialchars($data['customer_name'] ?? 'Customer') . ", your full online payment is confirmed!</p>";
                $body .= "</div>";

                // Prominent Notice: Work is Due
                $body .= "<div style='background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 14px; margin-bottom: 18px; color: #92400e; font-size: 13.5px; line-height: 1.5;'>";
                $body .= "<strong>Notice: Work is Due (Scheduled & Pending On-Site Visit)</strong><br>";
                $body .= "We have received your 100% full payment online. Your service is confirmed and scheduled for <strong>" . htmlspecialchars($data['preferred_date'] ?? '') . " (" . htmlspecialchars($data['preferred_time'] ?? '') . ")</strong>. Once our technician completes the work on site, your final completion certificate will be issued.";
                $body .= "</div>";

                $body .= "<div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; margin-bottom: 22px; font-size: 14px;'>";
                $body .= "<div style='display: flex; justify-content: space-between; margin-bottom: 8px;'><strong style='color: #0a1c33;'>Invoice Ref:</strong> <span style='font-family: monospace; font-weight: 700;'>" . htmlspecialchars($data['invoice_no'] ?? '') . "</span></div>";
                $body .= "<div style='display: flex; justify-content: space-between; margin-bottom: 8px;'><strong style='color: #0a1c33;'>Booking ID:</strong> <span style='font-family: monospace; font-weight: 700; color: #f25b29;'>#" . htmlspecialchars($data['booking_no'] ?? '') . "</span></div>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #0a1c33;'>Service:</strong> " . htmlspecialchars($data['service_name'] ?? 'Home Service') . "</div>";
                $body .= "<div style='display: flex; justify-content: space-between; padding-top: 10px; border-top: 1px dashed #cbd5e1; margin-top: 10px;'><strong style='color: #0a1c33;'>Total Paid (Online):</strong> <span style='font-size: 18px; font-weight: 800; color: #16a34a;'>₹" . number_format((float)($data['total'] ?? 0), 2) . "</span></div>";
                $body .= "</div>";

                $body .= "<div style='text-align: center; margin: 24px 0;'>";
                $body .= "<a href='" . htmlspecialchars($invoiceUrl) . "' style='background: #0a1c33; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14px; padding: 12px 28px; border-radius: 8px; display: inline-block;'>View Invoice in Account &rarr;</a>";
                $body .= "</div>";
                break;

            case 'invoice_final_completed':
                $body .= "<div style='text-align: center; margin-bottom: 20px;'>";
                $body .= "<span style='background: #dcfce7; color: #15803d; font-weight: 700; font-size: 12px; padding: 5px 12px; border-radius: 20px; display: inline-block;'>Service Completed &bull; Paid in Full</span>";
                $body .= "<h2 style='color: #0a1c33; font-size: 20px; margin: 12px 0 6px;'>Final GST Tax Invoice</h2>";
                $body .= "<p style='color: #64748b; font-size: 14px; margin: 0;'>Dear " . htmlspecialchars($data['customer_name'] ?? 'Customer') . ", your service is fully completed!</p>";
                $body .= "</div>";

                $body .= "<div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; margin-bottom: 22px; font-size: 14px;'>";
                $body .= "<div style='display: flex; justify-content: space-between; margin-bottom: 8px;'><strong style='color: #0a1c33;'>Tax Invoice #:</strong> <span style='font-family: monospace; font-weight: 700; color: #0a1c33;'>" . htmlspecialchars($data['invoice_no'] ?? '') . "</span></div>";
                $body .= "<div style='display: flex; justify-content: space-between; margin-bottom: 8px;'><strong style='color: #0a1c33;'>Booking ID:</strong> <span style='font-family: monospace; font-weight: 700; color: #f25b29;'>#" . htmlspecialchars($data['booking_no'] ?? '') . "</span></div>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #0a1c33;'>Service Executed:</strong> " . htmlspecialchars($data['service_name'] ?? 'Home Service') . "</div>";
                if (!empty($data['staff_name'])) {
                    $body .= "<div style='margin-bottom: 8px;'><strong style='color: #0a1c33;'>Completed By:</strong> " . htmlspecialchars($data['staff_name']) . "</div>";
                }
                if (!empty($data['work_summary'])) {
                    $body .= "<div style='margin-bottom: 8px; background: #ffffff; padding: 10px; border-radius: 6px; border: 1px solid #e2e8f0;'><strong>Work Notes:</strong> " . htmlspecialchars($data['work_summary']) . "</div>";
                }
                $body .= "<div style='display: flex; justify-content: space-between; padding-top: 10px; border-top: 1px dashed #cbd5e1; margin-top: 10px;'><strong style='color: #0a1c33;'>Total Amount Settled:</strong> <span style='font-size: 18px; font-weight: 800; color: #16a34a;'>₹" . number_format((float)($data['total'] ?? 0), 2) . "</span></div>";
                $body .= "</div>";

                $body .= "<div style='text-align: center; margin: 24px 0;'>";
                $body .= "<a href='" . htmlspecialchars($invoiceUrl) . "' style='background: #f25b29; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14px; padding: 12px 28px; border-radius: 8px; display: inline-block; box-shadow: 0 4px 12px rgba(242,91,41,0.25);'>Download Official GST Invoice &rarr;</a>";
                $body .= "</div>";
                break;

            case 'password_reset':
                $body .= "<h3 style='color: #0a1c33;'>Password Reset Request</h3>";
                $body .= "<p>You requested a password reset for your REFIXEL account.</p>";
                $body .= "<p style='margin: 20px 0;'><a href='" . htmlspecialchars($data['reset_link'] ?? '#') . "' style='display:inline-block; padding: 12px 24px; background: #f25b29; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold;'>Reset Password &rarr;</a></p>";
                $body .= "<p style='font-size: 12px; color: #64748b;'>This link expires in 60 minutes. If you did not make this request, you can safely ignore this email.</p>";
                break;

            case 'partner_application_received':
                $body .= "<div style='margin-bottom: 20px; text-align: center;'>";
                $body .= "<span style='background: #fef3c7; color: #b45309; font-weight: 700; font-size: 12px; padding: 5px 12px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.5px;'>Application Under Review</span>";
                $body .= "<h2 style='color: #0a1c33; font-size: 20px; margin: 12px 0 6px;'>Thank you for applying, " . htmlspecialchars($data['name'] ?? 'Partner') . "!</h2>";
                $body .= "<p style='color: #64748b; font-size: 14px; margin: 0; line-height: 1.6;'>Your partner application has been submitted to the REFIXEL Network and is currently <strong>under review / under process</strong> by our administration team.</p>";
                $body .= "</div>";

                $body .= "<div style='background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 10px; padding: 18px 20px; margin-bottom: 20px;'>";
                $body .= "<div style='font-size: 12px; color: #475569; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 12px; border-bottom: 1px solid #e2e8f0; padding-bottom: 6px;'>Submitted Application Summary</div>";
                $body .= "<div style='display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;'><span style='color: #64748b;'>Full Name:</span><span style='font-weight: 700; color: #0f172a;'>" . htmlspecialchars($data['name'] ?? '') . "</span></div>";
                $body .= "<div style='display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;'><span style='color: #64748b;'>Contact Phone:</span><span style='font-weight: 700; color: #0f172a;'>" . htmlspecialchars($data['phone'] ?? '') . "</span></div>";
                $body .= "<div style='display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;'><span style='color: #64748b;'>Email Address:</span><span style='font-weight: 600; color: #0284c7;'>" . htmlspecialchars($data['email'] ?? '') . "</span></div>";
                $body .= "<div style='display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;'><span style='color: #64748b;'>Specialized Trade:</span><span style='font-weight: 600; color: #0f172a;'>" . htmlspecialchars($data['trade'] ?? '') . "</span></div>";
                $body .= "<div style='display: flex; justify-content: space-between; font-size: 13px;'><span style='color: #64748b;'>Operational City:</span><span style='font-weight: 600; color: #0f172a;'>" . htmlspecialchars($data['city'] ?? '') . "</span></div>";
                $body .= "</div>";

                $body .= "<div style='background: #fff7ed; border-left: 4px solid #f97316; padding: 14px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 13.5px; color: #9a3412; line-height: 1.5;'>";
                $body .= "<strong>Next Steps:</strong> Please be patient while our verification team reviews your application. As soon as your profile is approved by the admin, you will receive an official approval email with your <strong>Technician Portal Login ID & Password</strong>.";
                $body .= "</div>";
                break;

            case 'partner_approved':
            case 'partner_welcome':
                $body .= "<div style='margin-bottom: 20px; text-align: center;'>";
                $body .= "<span style='background: #dcfce7; color: #15803d; font-weight: 700; font-size: 12px; padding: 5px 12px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.5px;'>Profile Approved & Activated</span>";
                $body .= "<h2 style='color: #0a1c33; font-size: 20px; margin: 12px 0 6px;'>Congratulations, " . htmlspecialchars($data['name'] ?? 'Partner') . "!</h2>";
                $body .= "<p style='color: #64748b; font-size: 14px; margin: 0; line-height: 1.6;'>Your partner application has been approved by the REFIXEL administration. Your technician portal access is now active and ready for dispatch orders.</p>";
                $body .= "</div>";

                $body .= "<div style='background: #0a1c33; border-radius: 10px; padding: 18px 20px; margin-bottom: 22px; color: #ffffff;'>";
                $body .= "<div style='font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 8px;'>Technician Portal Login Credentials</div>";
                $body .= "<div style='display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;'><span style='color: #94a3b8;'>Login ID (Phone):</span><span style='font-weight: 700; font-family: monospace; color: #38bdf8;'>" . htmlspecialchars($data['phone'] ?? '') . "</span></div>";
                $body .= "<div style='display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;'><span style='color: #94a3b8;'>Registered Email:</span><span style='font-weight: 700; color: #ffffff;'>" . htmlspecialchars($data['email'] ?? '') . "</span></div>";
                if (!empty($data['password'])) {
                    $body .= "<div style='display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;'><span style='color: #94a3b8;'>Portal Password:</span><span style='font-family: monospace; font-weight: 700; color: #fde047; letter-spacing: 1px;'>" . htmlspecialchars($data['password']) . "</span></div>";
                }
                $body .= "<div style='display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;'><span style='color: #94a3b8;'>Primary Trade:</span><span style='font-weight: 600; color: #ffffff;'>" . htmlspecialchars($data['trade'] ?? '') . "</span></div>";
                $body .= "<div style='display: flex; justify-content: space-between; font-size: 13px;'><span style='color: #94a3b8;'>City:</span><span style='font-weight: 600; color: #ffffff;'>" . htmlspecialchars($data['city'] ?? '') . "</span></div>";
                $body .= "</div>";

                $body .= "<div style='text-align: center; margin: 24px 0;'>";
                $body .= "<a href='" . htmlspecialchars($loginUrl) . "' style='background: #f25b29; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 13px 32px; border-radius: 8px; display: inline-block; box-shadow: 0 4px 12px rgba(242,91,41,0.3);'>Log In to Technician Portal &rarr;</a>";
                $body .= "</div>";
                break;

            case 'partner_rejected':
                $body .= "<div style='margin-bottom: 20px; text-align: center;'>";
                $body .= "<span style='background: #fee2e2; color: #b91c1c; font-weight: 700; font-size: 12px; padding: 5px 12px; border-radius: 6px; text-transform: uppercase; letter-spacing: 0.5px;'>Application Status Update</span>";
                $body .= "<h2 style='color: #0a1c33; font-size: 20px; margin: 12px 0 6px;'>Partner Application Update</h2>";
                $body .= "<p style='color: #64748b; font-size: 14px; margin: 0; line-height: 1.6;'>Dear " . htmlspecialchars($data['name'] ?? 'Applicant') . ", thank you for your interest in partnering with REFIXEL.</p>";
                $body .= "</div>";

                $body .= "<div style='background: #fef2f2; border: 1.5px solid #fecaca; border-radius: 10px; padding: 18px 20px; margin-bottom: 20px; color: #991b1b; font-size: 13.5px; line-height: 1.6;'>";
                $body .= "<p style='margin: 0 0 10px 0;'>After reviewing your profile and credentials, we regret to inform you that we are unable to approve your technician application at this time.</p>";
                if (!empty($data['reason'])) {
                    $body .= "<p style='margin: 0; padding-top: 10px; border-top: 1px dashed #fca5a5;'><strong>Feedback / Reason:</strong> " . htmlspecialchars($data['reason']) . "</p>";
                }
                $body .= "</div>";

                $body .= "<p style='font-size: 13px; color: #64748b; line-height: 1.5; text-align: center;'>If you have questions or would like to submit updated credentials, please contact our support team at <a href='mailto:contact@refixel.com' style='color:#f25b29; text-decoration: none; font-weight: 600;'>contact@refixel.com</a>.</p>";
                break;

            case 'admin_new_partner':
                $body .= "<div style='margin-bottom: 18px;'>";
                $body .= "<span style='background: #fef3c7; color: #b45309; font-weight: 700; font-size: 12px; padding: 4px 10px; border-radius: 6px; text-transform: uppercase;'>New Partner Registered</span>";
                $body .= "<h2 style='color: #0a1c33; font-size: 20px; margin: 10px 0 6px;'>New Technician Application Received</h2>";
                $body .= "<p style='color: #64748b; font-size: 14px; margin: 0;'>A new service technician has joined through the partner registration form:</p>";
                $body .= "</div>";

                $body .= "<div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; margin-bottom: 22px; font-size: 14px;'>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #0a1c33;'>Technician Name:</strong> " . htmlspecialchars($data['name'] ?? '') . "</div>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #0a1c33;'>Email:</strong> <a href='mailto:" . htmlspecialchars($data['email'] ?? '') . "' style='color: #0284c7;'>" . htmlspecialchars($data['email'] ?? '') . "</a></div>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #0a1c33;'>Phone:</strong> " . htmlspecialchars($data['phone'] ?? '') . "</div>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #0a1c33;'>Primary Trade:</strong> " . htmlspecialchars($data['trade'] ?? '') . "</div>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #0a1c33;'>City:</strong> " . htmlspecialchars($data['city'] ?? '') . "</div>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #0a1c33;'>Experience:</strong> " . htmlspecialchars($data['experience'] ?? '') . " Years</div>";
                $body .= "</div>";

                $body .= "<div style='text-align: center; margin: 24px 0;'>";
                $body .= "<a href='" . htmlspecialchars(View::fullUrl('/admin/staff')) . "' style='background: #0a1c33; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14px; padding: 12px 28px; border-radius: 8px; display: inline-block;'>View in Admin Staff Directory &rarr;</a>";
                $body .= "</div>";
                break;

            case 'enquiry_email_otp':
                $otpCode = htmlspecialchars((string)($data['otp'] ?? '000000'));
                $serviceName = htmlspecialchars((string)($data['service'] ?? 'Home Maintenance Service'));
                $customerName = htmlspecialchars((string)($data['name'] ?? 'Valued Customer'));

                $body .= "<div style='text-align: center; margin-bottom: 24px;'>";
                $body .= "<div style='display: inline-block; width: 64px; height: 64px; line-height: 64px; border-radius: 50%; background: #fff7ed; color: #f25b29; font-size: 28px; margin-bottom: 12px; box-shadow: 0 4px 12px rgba(242,91,41,0.15);'><i style='font-style: normal;'>🔐</i></div><br>";
                $body .= "<span style='background: #ffedd5; color: #c2410c; font-weight: 700; font-size: 12px; padding: 5px 14px; border-radius: 20px; display: inline-block; text-transform: uppercase; letter-spacing: 0.5px;'>Email Verification Required</span>";
                $body .= "<h2 style='color: #0a1c33; font-size: 22px; margin: 12px 0 6px;'>Verify Your Service Enquiry</h2>";
                $body .= "<p style='color: #64748b; font-size: 14px; margin: 0;'>Hello " . $customerName . ", thank you for choosing REFIXEL.</p>";
                $body .= "</div>";

                $body .= "<div style='background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 22px; text-align: center; margin-bottom: 22px;'>";
                $body .= "<p style='color: #475569; font-size: 14px; margin: 0 0 16px;'>Please enter this 6-digit One-Time Password (OTP) on the website to confirm your enquiry for <strong>" . $serviceName . "</strong>:</p>";
                $body .= "<div style='display: inline-block; background: #ffffff; border: 2px dashed #f25b29; border-radius: 12px; padding: 14px 32px; letter-spacing: 8px; font-size: 34px; font-weight: 800; font-family: Monaco, Menlo, Consolas, monospace; color: #f25b29; box-shadow: 0 2px 8px rgba(242,91,41,0.1);'>" . $otpCode . "</div>";
                $body .= "<p style='color: #94a3b8; font-size: 12px; margin: 14px 0 0;'><i style='font-style: normal;'>⏱️</i> This OTP code is valid for <strong>10 minutes</strong>. Do not share it with anyone.</p>";
                $body .= "</div>";

                $body .= "<div style='background: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 6px; font-size: 13px; color: #92400e; line-height: 1.5; margin-bottom: 20px;'>";
                $body .= "<strong>Why verify?</strong> We verify email addresses to ensure genuine customer requests and enable our dispatch team to coordinate your service without delay.";
                $body .= "</div>";

                $body .= "<p style='font-size: 12px; color: #94a3b8; text-align: center; margin: 0;'>If you did not request this service enquiry on REFIXEL, you can safely ignore this email.</p>";
                break;

            default:
                $body .= "<p style='font-size: 15px; color: #334155;'>" . htmlspecialchars($data['message'] ?? 'Notification from REFIXEL') . "</p>";
                break;
        }

        $body .= "</div>";

        // Brand Footer
        $body .= "<div style='background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 18px 24px; font-size: 12px; color: #64748b; text-align: center; line-height: 1.5;'>";
        $body .= "<p style='margin: 0 0 6px;'>&copy; " . date('Y') . " REFIXEL Home Services. All rights reserved.</p>";
        $body .= "<p style='margin: 0;'>For support, email <a href='mailto:contact@refixel.com' style='color: #f25b29; text-decoration: none;'>contact@refixel.com</a> or visit <a href='" . htmlspecialchars(View::fullUrl('/')) . "' style='color: #0a1c33; font-weight: 600;'>refixel.com</a></p>";
        $body .= "</div>";

        $body .= "</div></body></html>";

        return $body;
    }
}
