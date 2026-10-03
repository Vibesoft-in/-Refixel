<?php
declare(strict_types=1);

namespace App\Services;

use App\Core\Env;
use App\Core\Logger;

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
        $this->port        = (int)Env::get('MAIL_PORT', 2525);
        $this->username    = (string)Env::get('MAIL_USERNAME', '');
        $this->password    = (string)Env::get('MAIL_PASSWORD', '');
        $this->encryption  = (string)Env::get('MAIL_ENCRYPTION', 'tls');
        $this->fromAddress = (string)Env::get('MAIL_FROM_ADDRESS', 'bookings@primodomus.com');
        $this->fromName    = (string)Env::get('MAIL_FROM_NAME', 'Primodomus Service Platform');
    }

    public function send(string $to, string $subject, string $htmlBody, array $headers = []): bool
    {
        if (empty($this->username) || Env::get('MAIL_MAILER') === 'mock' || Env::get('APP_ENV') === 'test') {
            Logger::info("Mock Email sent to {$to} [Subject: {$subject}]");
            return true;
        }

        // Standard PHP mail with HTML headers fallback if SMTP socket isn't actively listening
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
            return @mail($to, $subject, $htmlBody, $headerStr);
        } catch (\Throwable $e) {
            Logger::error("SMTP mail dispatch error to {$to}: " . $e->getMessage());
            return false;
        }
    }

    public function sendTemplate(string $to, string $template, array $data = []): bool
    {
        $subject = match ($template) {
            'booking_confirmation' => 'Your Primodomus Booking is Confirmed - #' . ($data['booking_no'] ?? ''),
            'admin_new_booking'    => 'New Doorstep Service Booking - #' . ($data['booking_no'] ?? ''),
            'staff_assigned'       => 'New Job Assigned - #' . ($data['booking_no'] ?? ''),
            'status_updated'       => 'Update on your Booking #' . ($data['booking_no'] ?? '') . ' - Status: ' . ($data['status'] ?? ''),
            'job_completed'        => 'Service Completed! Booking #' . ($data['booking_no'] ?? ''),
            'invoice_created'      => 'GST Tax Invoice Ready - ' . ($data['invoice_no'] ?? ''),
            'password_reset'       => 'Reset your Primodomus Password',
            'admin_alert'          => 'Primodomus Platform Alert: ' . ($data['title'] ?? 'Attention Required'),
            'booking_reminder'     => 'Upcoming Service Visit Reminder - #' . ($data['booking_no'] ?? ''),
            default                => 'Notification from Primodomus',
        };

        $htmlBody = $this->buildHtmlTemplate($template, $data);
        return $this->send($to, $subject, $htmlBody);
    }

    protected function buildHtmlTemplate(string $template, array $data): string
    {
        $body = "<div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>";
        $body .= "<div style='text-align: center; border-bottom: 2px solid #0f6e56; padding-bottom: 15px; margin-bottom: 20px;'>";
        $body .= "<h2 style='color: #0f6e56; margin: 0;'>PRIMODOMUS</h2>";
        $body .= "<small style='color: #666;'>Home Care & Mechanized Maintenance Services</small>";
        $body .= "</div>";

        $body .= "<div style='color: #333; line-height: 1.6;'>";
        switch ($template) {
            case 'booking_confirmation':
                $body .= "<h3 style='color: #1a1a1a;'>Your booking is confirmed!</h3>";
                $body .= "<p>Dear " . htmlspecialchars($data['customer_name'] ?? 'Customer') . ",</p>";
                $body .= "<p>Thank you for choosing Primodomus. We have scheduled your <strong>" . htmlspecialchars($data['service_name'] ?? 'Service') . "</strong> on <strong>" . htmlspecialchars($data['preferred_date'] ?? '') . " (" . htmlspecialchars($data['preferred_time'] ?? '') . ")</strong>.</p>";
                $body .= "<p><strong>Booking Ref:</strong> #" . htmlspecialchars($data['booking_no'] ?? '') . "<br>";
                $body .= "<strong>Address:</strong> " . htmlspecialchars($data['address'] ?? '') . "</p>";
                
                if (!empty($data['generated_password'])) {
                    $body .= "<div style='background-color: #f0fdf4; border: 1px solid #dcfce7; padding: 15px; border-radius: 8px; margin-top: 20px;'>";
                    $body .= "<h4 style='color: #166534; margin-top: 0;'>Account Created Successfully</h4>";
                    $body .= "<p style='margin-bottom: 5px;'>We have created an account for you to track your bookings:</p>";
                    $body .= "<p style='margin-top: 0;'><strong>Login ID:</strong> " . htmlspecialchars($data['identifier'] ?? '') . "<br>";
                    $body .= "<strong>Password:</strong> " . htmlspecialchars($data['generated_password']) . "</p>";
                    $body .= "<p style='font-size: 12px; color: #666; margin-bottom: 0;'>You can change this password after logging in.</p>";
                    $body .= "</div>";
                }
                break;

            case 'admin_new_booking':
                $body .= "<h3 style='color: #0f6e56;'>New Customer Booking Received</h3>";
                $body .= "<p>Booking #" . htmlspecialchars($data['booking_no'] ?? '') . " has been placed for <strong>" . htmlspecialchars($data['service_name'] ?? '') . "</strong>.</p>";
                $body .= "<p><strong>Customer:</strong> " . htmlspecialchars($data['customer_name'] ?? '') . " (" . htmlspecialchars($data['phone'] ?? '') . ")<br>";
                $body .= "<strong>City / Area:</strong> " . htmlspecialchars($data['city'] ?? '') . "<br>";
                $body .= "<strong>Scheduled:</strong> " . htmlspecialchars($data['preferred_date'] ?? '') . " (" . htmlspecialchars($data['preferred_time'] ?? '') . ")</p>";
                break;

            case 'staff_assigned':
                $body .= "<h3 style='color: #0f6e56;'>New Field Job Assigned</h3>";
                $body .= "<p>Hello " . htmlspecialchars($data['staff_name'] ?? 'Technician') . ",</p>";
                $body .= "<p>You have been assigned to service booking #" . htmlspecialchars($data['booking_no'] ?? '') . " (<strong>" . htmlspecialchars($data['service_name'] ?? '') . "</strong>).</p>";
                $body .= "<p><strong>Scheduled:</strong> " . htmlspecialchars($data['preferred_date'] ?? '') . " (" . htmlspecialchars($data['preferred_time'] ?? '') . ")<br>";
                $body .= "<strong>Address:</strong> " . htmlspecialchars($data['address'] ?? '') . "</p>";
                break;

            case 'status_updated':
                $body .= "<h3 style='color: #1a1a1a;'>Status Update on Booking #" . htmlspecialchars($data['booking_no'] ?? '') . "</h3>";
                $body .= "<p>Your booking status has transitioned to: <strong style='text-transform: uppercase; color: #0f6e56;'>" . htmlspecialchars(str_replace('_', ' ', $data['status'] ?? '')) . "</strong>.</p>";
                if (!empty($data['notes'])) {
                    $body .= "<p><em>Note: " . htmlspecialchars($data['notes']) . "</em></p>";
                }
                break;

            case 'job_completed':
                $body .= "<h3 style='color: #0f6e56;'>Service Completed Successfully!</h3>";
                $body .= "<p>Dear " . htmlspecialchars($data['customer_name'] ?? 'Customer') . ",</p>";
                $body .= "<p>Your <strong>" . htmlspecialchars($data['service_name'] ?? 'Service') . "</strong> under Booking #" . htmlspecialchars($data['booking_no'] ?? '') . " has been completed by your technician.</p>";
                $body .= "<p>You can view your service summary and download your GST invoice directly in your account.</p>";
                break;

            case 'invoice_created':
                $body .= "<h3 style='color: #0f6e56;'>GST Tax Invoice Issued</h3>";
                $body .= "<p>Dear " . htmlspecialchars($data['customer_name'] ?? 'Customer') . ",</p>";
                $body .= "<p>Tax Invoice <strong>" . htmlspecialchars($data['invoice_no'] ?? '') . "</strong> for ₹" . number_format((float)($data['total'] ?? 0), 2) . " is ready.</p>";
                $body .= "<p>Includes 18% GST (9% CGST + 9% SGST). You can view and print this invoice from your Primodomus customer portal.</p>";
                break;

            case 'password_reset':
                $body .= "<h3 style='color: #1a1a1a;'>Password Reset Request</h3>";
                $body .= "<p>You requested a password reset for your Primodomus account.</p>";
                $body .= "<p><a href='" . htmlspecialchars($data['reset_link'] ?? '#') . "' style='display:inline-block; padding: 10px 20px; background: #0f6e56; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold;'>Reset Password</a></p>";
                $body .= "<p><small>This link expires in 60 minutes. If you did not make this request, you can safely ignore this email.</small></p>";
                break;

            case 'booking_reminder':
                $body .= "<h3 style='color: #0f6e56;'>Upcoming Service Appointment Reminder</h3>";
                $body .= "<p>This is a friendly reminder that your Primodomus service visit for <strong>" . htmlspecialchars($data['service_name'] ?? '') . "</strong> is scheduled for <strong>" . htmlspecialchars($data['preferred_date'] ?? '') . " (" . htmlspecialchars($data['preferred_time'] ?? '') . ")</strong>.</p>";
                $body .= "<p>Our professional technician will arrive at your address: " . htmlspecialchars($data['address'] ?? '') . ".</p>";
                break;

            default:
                $body .= "<p>" . htmlspecialchars($data['message'] ?? 'Notification from Primodomus') . "</p>";
                break;
        }
        $body .= "</div>";

        $body .= "<div style='border-top: 1px solid #eee; margin-top: 25px; padding-top: 15px; font-size: 12px; color: #888; text-align: center;'>";
        $body .= "<p>&copy; " . date('Y') . " Primodomus. All rights reserved.<br>For support, email care@primodomus.com or call +91 99533 58855.</p>";
        $body .= "</div></div>";

        return $body;
    }
}
