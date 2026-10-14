<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\Setting;
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

    protected static function getAdminEmail(): string
    {
        return (string)(
            Setting::get('admin_notification_email')
            ?: Setting::get('company_email')
            ?: Env::get('ADMIN_NOTIFICATION_EMAIL')
            ?: Env::get('MAIL_FROM_ADDRESS')
            ?: 'wearerefixel@gmail.com'
        );
    }

    // ==========================================
    // LIFECYCLE NOTIFICATION EVENTS
    // ==========================================

    /**
     * Event 1: New Booking -> Admin Alert with Direct Dashboard Link
     */
    public static function notifyNewBookingAdmin(array $booking): void
    {
        $adminEmail = self::getAdminEmail();
        $bId = $booking['id'] ?? $booking['booking_id'] ?? '';
        $adminUrl = !empty($booking['admin_url']) ? $booking['admin_url'] : View::fullUrl('/admin/bookings/' . $bId);

        self::send('email', $adminEmail, 'admin_new_booking', [
            'booking_no'    => $booking['booking_no'] ?? '',
            'service_name'  => $booking['service_name'] ?? 'Home Service',
            'customer_name' => $booking['name'] ?? ($booking['customer_name'] ?? 'Customer'),
            'phone'         => $booking['phone'] ?? ($booking['customer_phone'] ?? ''),
            'email'         => $booking['email'] ?? ($booking['customer_email'] ?? ''),
            'city'          => $booking['city'] ?? 'Service Area',
            'preferred_date'=> $booking['preferred_date'] ?? date('Y-m-d'),
            'preferred_time'=> $booking['preferred_time'] ?? 'Flexible',
            'address'       => $booking['address'] ?? '',
            'message'       => $booking['message'] ?? ($booking['issue_details'] ?? ''),
            'admin_url'     => $adminUrl,
        ]);
    }

    /**
     * Event 2: Booking Confirmation -> Customer (Welcome Credentials if New User)
     */
    public static function notifyBookingConfirmationCustomer(array $booking): void
    {
        $isNew = !empty($booking['generated_password']) || !empty($booking['is_new_customer']);
        $template = $isNew ? 'customer_welcome_booking' : 'booking_confirmation';
        $bId = $booking['id'] ?? $booking['booking_id'] ?? '';

        $data = [
            'booking_no'         => $booking['booking_no'] ?? '',
            'customer_name'      => $booking['name'] ?? ($booking['customer_name'] ?? 'Customer'),
            'service_name'       => $booking['service_name'] ?? 'Home Service',
            'preferred_date'     => $booking['preferred_date'] ?? date('Y-m-d'),
            'preferred_time'     => $booking['preferred_time'] ?? 'Flexible',
            'address'            => $booking['address'] ?? '',
            'phone'              => $booking['phone'] ?? '',
            'email'              => $booking['email'] ?? null,
            'generated_password' => $booking['generated_password'] ?? null,
            'identifier'         => $booking['identifier'] ?? ($booking['email'] ?? $booking['phone'] ?? null),
            'login_url'          => !empty($booking['login_url']) ? $booking['login_url'] : View::fullUrl('/login'),
            'booking_url'        => View::fullUrl('/account/bookings/' . $bId),
        ];

        if (!empty($booking['email'])) {
            self::send('email', $booking['email'], $template, $data);
        }

        if (!empty($booking['phone'])) {
            self::send('sms', $booking['phone'], 'booking_confirmed_sms', $data);

            if (Env::get('WHATSAPP_ENABLED', false)) {
                self::send('whatsapp', $booking['phone'], 'booking_confirmed_wa', $data);
            }
        }
    }

    /**
     * Event 3: Staff Assignment -> Technician Email with Job Details & Payout
     */
    public static function notifyStaffAssigned(array $booking, array $staff): void
    {
        $jobId = $booking['job_id'] ?? '';
        $baseAmt = (float)($booking['total_amount'] ?? $booking['starting_price'] ?? 899);
        $expectedPayout = max(0.0, round($baseAmt - 49.0, 2));

        $data = [
            'booking_no'        => $booking['booking_no'] ?? '',
            'staff_name'        => $staff['name'] ?? 'Technician',
            'service_name'      => $booking['service_name'] ?? 'Home Service',
            'preferred_date'    => $booking['preferred_date'] ?? date('Y-m-d'),
            'preferred_time'    => $booking['preferred_time'] ?? 'Flexible',
            'address'           => $booking['address'] ?? '',
            'technician_payout' => $expectedPayout,
            'staff_job_url'     => View::fullUrl('/staff/jobs/' . $jobId),
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
     * Event 4: Technician Accepts Job -> Notify Both Customer and Admin
     */
    public static function notifyTechnicianAccepted(array $booking, array $staff): void
    {
        $bId = $booking['id'] ?? $booking['booking_id'] ?? '';
        $data = [
            'booking_no'     => $booking['booking_no'] ?? '',
            'customer_name'  => $booking['name'] ?? ($booking['customer_name'] ?? 'Customer'),
            'customer_phone' => $booking['phone'] ?? ($booking['customer_phone'] ?? ''),
            'service_name'   => $booking['service_name'] ?? 'Home Service',
            'staff_name'     => $staff['name'] ?? ($booking['staff_name'] ?? 'Technician'),
            'staff_phone'    => $staff['phone'] ?? ($booking['staff_phone'] ?? ''),
            'preferred_date' => $booking['preferred_date'] ?? date('Y-m-d'),
            'preferred_time' => $booking['preferred_time'] ?? 'Flexible',
            'address'        => $booking['address'] ?? '',
            'booking_url'    => View::fullUrl('/account/bookings/' . $bId),
            'admin_url'      => View::fullUrl('/admin/bookings/' . $bId),
        ];

        // 1. Notify Customer
        $customerEmail = $booking['email'] ?? ($booking['customer_email'] ?? null);
        if (!empty($customerEmail)) {
            self::send('email', $customerEmail, 'technician_accepted_customer', $data);
        }

        // 2. Notify Admin
        $adminEmail = self::getAdminEmail();
        self::send('email', $adminEmail, 'technician_accepted_admin', $data);
    }

    /**
     * Event 5: Prepaid Online Booking Receipt with "Work is Due" Notice
     */
    public static function notifyPrepaidInvoiceWorkDue(array $invoice, array $booking): void
    {
        $customerEmail = $booking['email'] ?? ($booking['customer_email'] ?? null);
        if (empty($customerEmail)) {
            return;
        }

        $invId = $invoice['id'] ?? '';
        $data = [
            'invoice_no'    => $invoice['invoice_no'] ?? 'INV-PREPAID',
            'booking_no'    => $booking['booking_no'] ?? '',
            'customer_name' => $booking['name'] ?? ($booking['customer_name'] ?? 'Customer'),
            'service_name'  => $booking['service_name'] ?? 'Home Service',
            'total'         => (float)($invoice['total'] ?? ($booking['total_amount'] ?? 0)),
            'preferred_date'=> $booking['preferred_date'] ?? date('Y-m-d'),
            'preferred_time'=> $booking['preferred_time'] ?? 'Flexible',
            'invoice_url'   => View::fullUrl('/account/invoices/' . $invId),
        ];

        self::send('email', $customerEmail, 'invoice_prepaid_work_due', $data);
    }

    /**
     * Event 6: Service Completed -> Final GST Tax Invoice to Customer
     */
    public static function notifyFinalCompletedInvoice(array $invoice, array $booking, ?array $staff = null): void
    {
        $customerEmail = $booking['email'] ?? ($booking['customer_email'] ?? null);
        if (empty($customerEmail)) {
            return;
        }

        $invId = $invoice['id'] ?? '';
        $data = [
            'invoice_no'    => $invoice['invoice_no'] ?? 'INV-FINAL',
            'booking_no'    => $booking['booking_no'] ?? '',
            'customer_name' => $booking['name'] ?? ($booking['customer_name'] ?? 'Customer'),
            'service_name'  => $booking['service_name'] ?? 'Home Service',
            'staff_name'    => $staff['name'] ?? ($booking['staff_name'] ?? 'Technician'),
            'work_summary'  => $booking['work_summary'] ?? 'Service completed satisfactorily on site.',
            'total'         => (float)($invoice['total'] ?? ($booking['total_amount'] ?? 0)),
            'advance_paid'  => (float)($invoice['advance_paid'] ?? ($booking['advance_amount'] ?? 0)),
            'due_amount'    => (float)($invoice['due_amount'] ?? 0),
            'invoice_url'   => View::fullUrl('/account/invoices/' . $invId),
        ];

        self::send('email', $customerEmail, 'invoice_final_completed', $data);
    }

    /**
     * Event 7: Booking Status Change -> Customer
     */
    public static function notifyBookingStatusChange(array $booking, string $newStatus, ?string $notes = null): void
    {
        $data = [
            'booking_no'    => $booking['booking_no'] ?? '',
            'customer_name' => $booking['name'] ?? ($booking['customer_name'] ?? 'Customer'),
            'status'        => $newStatus,
            'notes'         => $notes ?? '',
        ];

        $email = $booking['email'] ?? ($booking['customer_email'] ?? null);
        if (!empty($email)) {
            self::send('email', $email, 'status_updated', $data);
        }

        $phone = $booking['phone'] ?? ($booking['customer_phone'] ?? null);
        if (!empty($phone)) {
            self::send('sms', $phone, 'status_updated_sms', $data);
        }
    }

    /**
     * Event 8: Job Completed -> Customer & Admin (General Alert)
     */
    public static function notifyJobCompleted(array $booking, ?array $staff = null): void
    {
        $data = [
            'booking_no'    => $booking['booking_no'] ?? '',
            'customer_name' => $booking['name'] ?? ($booking['customer_name'] ?? 'Customer'),
            'service_name'  => $booking['service_name'] ?? 'Home Service',
            'staff_name'    => $staff['name'] ?? ($booking['staff_name'] ?? 'Technician'),
        ];

        $email = $booking['email'] ?? ($booking['customer_email'] ?? null);
        if (!empty($email)) {
            self::send('email', $email, 'job_completed', $data);
        }

        // Alert Admin
        $adminEmail = self::getAdminEmail();
        self::send('email', $adminEmail, 'admin_alert', [
            'title'   => "Job Completed: Booking #{$booking['booking_no']}",
            'message' => "Technician {$data['staff_name']} completed service for {$data['customer_name']}.",
        ]);
    }

    /**
     * Event 9: GST Invoice Created -> Customer
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
     * Event 10: Password Reset -> Customer / Staff
     */
    public static function notifyPasswordReset(string $email, string $resetLink): void
    {
        self::send('email', $email, 'password_reset', [
            'reset_link' => $resetLink,
        ]);
    }

    /**
     * Event 11: Important Admin Alert
     */
    public static function notifyAdminAlert(string $title, string $message, array $context = []): void
    {
        $adminEmail = self::getAdminEmail();
        self::send('email', $adminEmail, 'admin_alert', [
            'title'   => $title,
            'message' => $message,
            'context' => $context,
        ]);
    }

    /**
     * Event 12: Upcoming Booking Reminder (Cron trigger)
     */
    public static function notifyUpcomingReminder(array $booking): void
    {
        $data = [
            'booking_no'    => $booking['booking_no'] ?? '',
            'customer_name' => $booking['name'] ?? ($booking['customer_name'] ?? 'Customer'),
            'service_name'  => $booking['service_name'] ?? 'Home Service',
            'preferred_date'=> $booking['preferred_date'] ?? '',
            'preferred_time'=> $booking['preferred_time'] ?? '',
            'address'       => $booking['address'] ?? '',
        ];

        $email = $booking['email'] ?? ($booking['customer_email'] ?? null);
        if (!empty($email)) {
            self::send('email', $email, 'booking_reminder', $data);
        }

        $phone = $booking['phone'] ?? ($booking['customer_phone'] ?? null);
        if (!empty($phone)) {
            self::send('sms', $phone, 'booking_reminder_sms', $data);
        }
    }

    /**
     * Event 13: Partner/Technician Registration Welcome
     */
    public static function notifyPartnerWelcome(array $staffData): void
    {
        if (!empty($staffData['email'])) {
            self::send('email', $staffData['email'], 'partner_welcome', $staffData);
        }
    }

    /**
     * Event 14: New Partner Registration Alert to Admin
     */
    public static function notifyAdminNewPartner(array $staffData): void
    {
        $adminEmail = Setting::get('admin_notification_email') ?: (Setting::get('company_email') ?: 'contact@refixel.com');
        if (!empty($adminEmail)) {
            self::send('email', $adminEmail, 'admin_new_partner', $staffData);
        }
    }
}
