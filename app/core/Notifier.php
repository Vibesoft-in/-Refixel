<?php
declare(strict_types=1);

namespace App\Core;

use App\Services\EmailProviderInterface;
use App\Services\MetaWhatsAppService;
use App\Services\Msg91SmsService;
use App\Services\SmsProviderInterface;
use App\Services\SmtpEmailService;
use App\Services\WhatsAppProviderInterface;

class Notifier
{
    protected static ?EmailProviderInterface $emailProvider = null;
    protected static ?SmsProviderInterface $smsProvider = null;
    protected static ?WhatsAppProviderInterface $whatsappProvider = null;

    public static function setEmailProvider(EmailProviderInterface $provider): void
    {
        self::$emailProvider = $provider;
    }

    public static function setSmsProvider(SmsProviderInterface $provider): void
    {
        self::$smsProvider = $provider;
    }

    public static function setWhatsAppProvider(WhatsAppProviderInterface $provider): void
    {
        self::$whatsappProvider = $provider;
    }

    public static function getEmailProvider(): EmailProviderInterface
    {
        if (self::$emailProvider === null) {
            self::$emailProvider = new SmtpEmailService();
        }
        return self::$emailProvider;
    }

    public static function getSmsProvider(): SmsProviderInterface
    {
        if (self::$smsProvider === null) {
            self::$smsProvider = new Msg91SmsService();
        }
        return self::$smsProvider;
    }

    public static function getWhatsAppProvider(): WhatsAppProviderInterface
    {
        if (self::$whatsappProvider === null) {
            self::$whatsappProvider = new MetaWhatsAppService();
        }
        return self::$whatsappProvider;
    }

    /**
     * Dispatch notification to specified channel and log in database.
     */
    public static function send(string $channel, string $to, string $template, array $data = []): bool
    {
        $status = 'sent';
        $error = null;

        try {
            $success = match ($channel) {
                'email'    => self::getEmailProvider()->sendTemplate($to, $template, $data),
                'sms'      => self::getSmsProvider()->sendTransactional($to, $template, $data),
                'whatsapp' => self::getWhatsAppProvider()->sendTemplateMessage($to, $template, array_values($data)),
                default    => throw new \InvalidArgumentException("Unsupported notification channel: {$channel}"),
            };

            if (!$success) {
                $status = 'failed';
                $error = 'Provider returned false / non-zero error';
            }
        } catch (\Throwable $e) {
            $status = 'failed';
            $error = $e->getMessage();
            Logger::error("Notification failed [{$channel} to {$to}]: " . $error);
        }

        // Record in notifications audit log table
        try {
            Database::query(
                "INSERT INTO notifications (channel, to_address, template, payload, status, error_message, created_at)
                 VALUES (:channel, :to_address, :template, :payload, :status, :error_message, NOW())",
                [
                    'channel'       => $channel,
                    'to_address'    => $to,
                    'template'      => $template,
                    'payload'       => json_encode($data),
                    'status'        => $status,
                    'error_message' => $error,
                ]
            );
        } catch (\Throwable $dbEx) {
            Logger::error("Failed to log notification to database: " . $dbEx->getMessage());
        }

        return $status === 'sent';
    }

    // ==========================================
    // LIFECYCLE NOTIFICATION EVENTS (Prompt 11)
    // ==========================================

    /**
     * Event 1: New Booking -> Admin Alert
     */
    public static function notifyNewBookingAdmin(array $booking): void
    {
        $adminEmail = (string)Env::get('ADMIN_NOTIFICATION_EMAIL', Env::get('MAIL_FROM_ADDRESS', 'admin@REFIXEL.com'));
        self::send('email', $adminEmail, 'admin_new_booking', [
            'booking_no'    => $booking['booking_no'] ?? '',
            'service_name'  => $booking['service_name'] ?? 'Home Service',
            'customer_name' => $booking['name'] ?? '',
            'phone'         => $booking['phone'] ?? '',
            'city'          => $booking['city'] ?? '',
            'preferred_date'=> $booking['preferred_date'] ?? '',
            'preferred_time'=> $booking['preferred_time'] ?? '',
            'address'       => $booking['address'] ?? '',
        ]);
    }

    /**
     * Event 2: Booking Confirmation -> Customer
     */
    public static function notifyBookingConfirmationCustomer(array $booking): void
    {
        $data = [
            'booking_no'    => $booking['booking_no'] ?? '',
            'customer_name' => $booking['name'] ?? 'Customer',
            'service_name'  => $booking['service_name'] ?? 'Home Service',
            'preferred_date'=> $booking['preferred_date'] ?? '',
            'preferred_time'=> $booking['preferred_time'] ?? '',
            'address'       => $booking['address'] ?? '',
            'generated_password' => $booking['generated_password'] ?? null,
            'identifier'    => $booking['identifier'] ?? null,
        ];

        if (!empty($booking['email'])) {
            self::send('email', $booking['email'], 'booking_confirmation', $data);
        }

        if (!empty($booking['phone'])) {
            self::send('sms', $booking['phone'], 'booking_confirmed_sms', $data);

            if (Env::get('WHATSAPP_ENABLED', false)) {
                self::send('whatsapp', $booking['phone'], 'booking_confirmed_wa', $data);
            }
        }
    }

    /**
     * Event 3: Staff Assignment -> Technician
     */
    public static function notifyStaffAssigned(array $booking, array $staff): void
    {
        $data = [
            'booking_no'    => $booking['booking_no'] ?? '',
            'staff_name'    => $staff['name'] ?? 'Technician',
            'service_name'  => $booking['service_name'] ?? 'Home Service',
            'preferred_date'=> $booking['preferred_date'] ?? '',
            'preferred_time'=> $booking['preferred_time'] ?? '',
            'address'       => $booking['address'] ?? '',
        ];

        if (!empty($staff['email'])) {
            self::send('email', $staff['email'], 'staff_assigned', $data);
        }

        if (!empty($staff['phone'])) {
            self::send('sms', $staff['phone'], 'staff_assigned_sms', $data);

            if (Env::get('WHATSAPP_ENABLED', false)) {
                self::send('whatsapp', $staff['phone'], 'staff_assigned_wa', $data);
            }
        }
    }

    /**
     * Event 4: Booking Status Change -> Customer
     */
    public static function notifyBookingStatusChange(array $booking, string $newStatus, ?string $notes = null): void
    {
        $data = [
            'booking_no'    => $booking['booking_no'] ?? '',
            'customer_name' => $booking['name'] ?? 'Customer',
            'status'        => $newStatus,
            'notes'         => $notes ?? '',
        ];

        if (!empty($booking['email'])) {
            self::send('email', $booking['email'], 'status_updated', $data);
        }

        if (!empty($booking['phone'])) {
            self::send('sms', $booking['phone'], 'status_updated_sms', $data);
        }
    }

    /**
     * Event 5: Job Completed -> Customer & Admin
     */
    public static function notifyJobCompleted(array $booking, ?array $staff = null): void
    {
        $data = [
            'booking_no'    => $booking['booking_no'] ?? '',
            'customer_name' => $booking['name'] ?? 'Customer',
            'service_name'  => $booking['service_name'] ?? 'Home Service',
            'staff_name'    => $staff['name'] ?? 'Technician',
        ];

        if (!empty($booking['email'])) {
            self::send('email', $booking['email'], 'job_completed', $data);
        }

        // Alert Admin
        $adminEmail = (string)Env::get('ADMIN_NOTIFICATION_EMAIL', Env::get('MAIL_FROM_ADDRESS', 'admin@REFIXEL.com'));
        self::send('email', $adminEmail, 'admin_alert', [
            'title'   => "Job Completed: Booking #{$booking['booking_no']}",
            'message' => "Technician {$data['staff_name']} completed service for {$data['customer_name']}.",
        ]);
    }

    /**
     * Event 6: GST Invoice Created -> Customer
     */
    public static function notifyInvoiceCreated(array $invoice, array $customer): void
    {
        $data = [
            'invoice_no'    => $invoice['invoice_no'] ?? '',
            'customer_name' => $customer['name'] ?? 'Customer',
            'total'         => $invoice['total'] ?? 0,
        ];

        if (!empty($customer['email'])) {
            self::send('email', $customer['email'], 'invoice_created', $data);
        }
    }

    /**
     * Event 7: Password Reset -> Customer / Staff
     */
    public static function notifyPasswordReset(string $email, string $resetLink): void
    {
        self::send('email', $email, 'password_reset', [
            'reset_link' => $resetLink,
        ]);
    }

    /**
     * Event 8: Important Admin Alert
     */
    public static function notifyAdminAlert(string $title, string $message, array $context = []): void
    {
        $adminEmail = (string)Env::get('ADMIN_NOTIFICATION_EMAIL', Env::get('MAIL_FROM_ADDRESS', 'admin@REFIXEL.com'));
        self::send('email', $adminEmail, 'admin_alert', [
            'title'   => $title,
            'message' => $message,
            'context' => $context,
        ]);
    }

    /**
     * Event 9: Upcoming Booking Reminder (Cron trigger)
     */
    public static function notifyUpcomingReminder(array $booking): void
    {
        $data = [
            'booking_no'    => $booking['booking_no'] ?? '',
            'customer_name' => $booking['name'] ?? 'Customer',
            'service_name'  => $booking['service_name'] ?? 'Home Service',
            'preferred_date'=> $booking['preferred_date'] ?? '',
            'preferred_time'=> $booking['preferred_time'] ?? '',
            'address'       => $booking['address'] ?? '',
        ];

        if (!empty($booking['email'])) {
            self::send('email', $booking['email'], 'booking_reminder', $data);
        }

        if (!empty($booking['phone'])) {
            self::send('sms', $booking['phone'], 'booking_reminder_sms', $data);
        }
    }
}

