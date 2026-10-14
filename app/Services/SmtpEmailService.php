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
        $this->fromAddress = (string)Env::get('MAIL_FROM_ADDRESS', 'wearerefixel@gmail.com');
        $this->fromName    = (string)Env::get('MAIL_FROM_NAME', 'REFIXEL Home Services');
    }

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

        // Attempt SMTP socket delivery if credentials are provided or mailer is smtp
        if (!empty($this->username) && !empty($this->password) || Env::get('MAIL_MAILER') === 'smtp') {
            $smtpOk = $this->sendViaSmtp($to, $subject, $htmlBody, $headers);
            if ($smtpOk) {
                return true;
            }
            Logger::warning("SMTP socket delivery failed for {$to}, falling back to PHP mail().");
        }

        // Fallback to standard PHP mail()
        return $this->sendViaPhpMail($to, $subject, $htmlBody, $headers);
    }

    protected function sendViaPhpMail(string $to, string $subject, string $htmlBody, array $headers = []): bool
    {
        $defaultHeaders = [
            'MIME-Version' => '1.0',
            'Content-type' => 'text/html; charset=UTF-8',
            'From'         => "{$this->fromName} <{$this->fromAddress}>",
            'Reply-To'     => $this->fromAddress,
            'X-Mailer'     => 'PHP/' . phpversion(),
        ];

        $allHeaders = array_merge($defaultHeaders, $headers);
        $headerStr = '';
        foreach ($allHeaders as $k => $v) {
            $headerStr .= "{$k}: {$v}\r\n";
        }

        try {
            $ok = @mail($to, $subject, $htmlBody, $headerStr);
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
            Logger::error("SMTP socket error to {$to}: " . $e->getMessage());
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
            'booking_confirmation'        => 'Your REFIXEL Booking is Confirmed - #' . ($data['booking_no'] ?? ''),
            'admin_new_booking'           => '🚨 New Booking Received: #' . ($data['booking_no'] ?? '') . ' (' . ($data['service_name'] ?? 'Home Service') . ')',
            'staff_assigned'              => '🔧 New Job Assigned: #' . ($data['booking_no'] ?? '') . ' - ' . ($data['service_name'] ?? 'Service'),
            'technician_accepted_customer'=> '✅ Technician Confirmed: Your Service is Scheduled - #' . ($data['booking_no'] ?? ''),
            'technician_accepted_admin'   => '✅ Technician Accepted Job: #' . ($data['booking_no'] ?? '') . ' - ' . ($data['staff_name'] ?? 'Technician'),
            'invoice_prepaid_work_due'    => '📄 Payment Receipt: Work Scheduled - Booking #' . ($data['booking_no'] ?? ''),
            'invoice_final_completed'     => '🧾 Final GST Tax Invoice: Service Completed - Booking #' . ($data['booking_no'] ?? ''),
            'status_updated'              => 'Update on your Booking #' . ($data['booking_no'] ?? '') . ' - Status: ' . ($data['status'] ?? ''),
            'job_completed'               => 'Service Completed! Booking #' . ($data['booking_no'] ?? ''),
            'invoice_created'             => 'GST Tax Invoice Ready - ' . ($data['invoice_no'] ?? ''),
            'password_reset'              => 'Reset your REFIXEL Password',
            'admin_alert'                 => 'REFIXEL Platform Alert: ' . ($data['title'] ?? 'Attention Required'),
            'booking_reminder'            => 'Upcoming Service Visit Reminder - #' . ($data['booking_no'] ?? ''),
            'partner_welcome'             => 'Welcome to REFIXEL Partner Network - Your Technician Portal Access',
            'admin_new_partner'           => '👷 New Service Partner Registered: ' . ($data['name'] ?? 'Technician') . ' (' . ($data['trade'] ?? 'Trade') . ')',
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

        $body = "<!DOCTYPE html><html><head><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'></head><body style='margin:0; padding:20px; background:#f4f6f9; font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Helvetica, Arial, sans-serif; color: #1e293b;'>";
        $body .= "<div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 4px 16px rgba(0,0,0,0.06);'>";
        
        // Brand Header
        $body .= "<div style='background: #0a1c33; padding: 24px 20px; text-align: center; border-bottom: 3px solid #f25b29;'>";
        $body .= "<h1 style='margin: 0; color: #ffffff; font-size: 26px; font-weight: 800; letter-spacing: 0.5px;'>REFIXEL<span style='color: #f25b29;'>.</span></h1>";
        $body .= "<p style='margin: 4px 0 0; color: #94a3b8; font-size: 12px; text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600;'>Mechanized Home & Maintenance Services</p>";
        $body .= "</div>";

        $body .= "<div style='padding: 28px 24px;'>";

        switch ($template) {
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

                $payoutDisplay = !empty($data['technician_payout']) ? '₹' . number_format((float)$data['technician_payout'], 0) : '₹850';

                $body .= "<div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; margin-bottom: 22px; font-size: 14px;'>";
                $body .= "<div style='display: flex; justify-content: space-between; margin-bottom: 8px;'><strong style='color: #0a1c33;'>Booking ID:</strong> <span style='font-family: monospace; font-weight: 700; color: #f25b29;'>#" . htmlspecialchars($data['booking_no'] ?? '') . "</span></div>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #0a1c33;'>Service:</strong> " . htmlspecialchars($data['service_name'] ?? 'Home Service') . "</div>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #0a1c33;'>Scheduled:</strong> " . htmlspecialchars($data['preferred_date'] ?? date('Y-m-d')) . " (" . htmlspecialchars($data['preferred_time'] ?? 'Flexible') . ")</div>";
                $body .= "<div style='margin-bottom: 8px;'><strong style='color: #0a1c33;'>Address:</strong> " . htmlspecialchars($data['address'] ?? '') . "</div>";
                $body .= "<div style='padding-top: 10px; border-top: 1px dashed #cbd5e1; margin-top: 10px;'><strong style='color: #15803d;'>Your Expected Payout:</strong> <span style='font-size: 18px; font-weight: 800; color: #16a34a;'>" . $payoutDisplay . "</span></div>";
                $body .= "</div>";

                $body .= "<div style='text-align: center; margin: 24px 0;'>";
                $body .= "<a href='" . htmlspecialchars($staffUrl) . "' style='background: #10b981; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 13px 30px; border-radius: 8px; display: inline-block;'>View & Accept Job &rarr;</a>";
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
                $body .= "<a href='" . htmlspecialchars($bookingUrl) . "' style='background: #0a1c33; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14px; padding: 12px 28px; border-radius: 8px; display: inline-block;'>View Booking in Portal &rarr;</a>";
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

            case 'partner_welcome':
                $body .= "<div style='margin-bottom: 20px;'>";
                $body .= "<span style='background: #ffedd5; color: #c2410c; font-weight: 700; font-size: 12px; padding: 4px 10px; border-radius: 6px; text-transform: uppercase;'>Partner Onboarding</span>";
                $body .= "<h2 style='color: #0a1c33; font-size: 20px; margin: 10px 0 6px;'>Welcome to REFIXEL, " . htmlspecialchars($data['name'] ?? 'Partner') . "!</h2>";
                $body .= "<p style='color: #64748b; font-size: 14px; margin: 0;'>Your technician registration has been received and activated. When new jobs are assigned to you by admin, all job details and dispatch alerts will be delivered immediately to this email.</p>";
                $body .= "</div>";

                $body .= "<div style='background: #0a1c33; border-radius: 10px; padding: 18px 20px; margin-bottom: 22px; color: #ffffff;'>";
                $body .= "<div style='font-size: 12px; color: #94a3b8; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 12px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 8px;'>Technician Portal Credentials</div>";
                $body .= "<div style='display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;'><span style='color: #94a3b8;'>Mobile / Login ID:</span><span style='font-weight: 700; font-family: monospace; color: #38bdf8;'>" . htmlspecialchars($data['phone'] ?? '') . "</span></div>";
                $body .= "<div style='display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;'><span style='color: #94a3b8;'>Registered Email:</span><span style='font-weight: 700; color: #ffffff;'>" . htmlspecialchars($data['email'] ?? '') . "</span></div>";
                if (!empty($data['password'])) {
                    $body .= "<div style='display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;'><span style='color: #94a3b8;'>Password:</span><span style='font-family: monospace; font-weight: 700; color: #fde047; letter-spacing: 1px;'>" . htmlspecialchars($data['password']) . "</span></div>";
                }
                $body .= "<div style='display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 13px;'><span style='color: #94a3b8;'>Primary Trade:</span><span style='font-weight: 600; color: #ffffff;'>" . htmlspecialchars($data['trade'] ?? '') . "</span></div>";
                $body .= "<div style='display: flex; justify-content: space-between; font-size: 13px;'><span style='color: #94a3b8;'>City:</span><span style='font-weight: 600; color: #ffffff;'>" . htmlspecialchars($data['city'] ?? '') . "</span></div>";
                $body .= "</div>";

                $body .= "<div style='text-align: center; margin: 24px 0;'>";
                $body .= "<a href='" . htmlspecialchars($loginUrl) . "' style='background: #f25b29; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 15px; padding: 13px 32px; border-radius: 8px; display: inline-block; box-shadow: 0 4px 12px rgba(242,91,41,0.3);'>Log In to Technician Portal &rarr;</a>";
                $body .= "</div>";
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

            default:
                $body .= "<p style='font-size: 15px; color: #334155;'>" . htmlspecialchars($data['message'] ?? 'Notification from REFIXEL') . "</p>";
                break;
        }

        $body .= "</div>";

        // Brand Footer
        $body .= "<div style='background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 18px 24px; font-size: 12px; color: #64748b; text-align: center; line-height: 1.5;'>";
        $body .= "<p style='margin: 0 0 6px;'>&copy; " . date('Y') . " REFIXEL Home Services. All rights reserved.</p>";
        $body .= "<p style='margin: 0;'>For support, email <a href='mailto:wearerefixel@gmail.com' style='color: #f25b29; text-decoration: none;'>wearerefixel@gmail.com</a> or visit <a href='" . htmlspecialchars(View::fullUrl('/')) . "' style='color: #0a1c33; font-weight: 600;'>refixel.com</a></p>";
        $body .= "</div>";

        $body .= "</div></body></html>";

        return $body;
    }
}
