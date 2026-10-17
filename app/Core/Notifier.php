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
        $email = (string)(
            Setting::get('admin_notification_email')
            ?: Setting::get('company_email')
            ?: Env::get('ADMIN_NOTIFICATION_EMAIL')
            ?: Env::get('MAIL_FROM_ADDRESS')
            ?: 'wearerefixel@gmail.com'
        );
        $email = trim($email);
        if (empty($email) || str_contains($email, 'primodomus.com') || str_contains($email, 'example.com')) {
            return 'wearerefixel@gmail.com';
        }
        return $email;
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

        $payload = [
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
        ];

        self::send('email', $adminEmail, 'admin_new_booking', $payload);

        // Also notify super admin mailbox if different
        if ($adminEmail !== 'wearerefixel@gmail.com') {
            self::send('email', 'wearerefixel@gmail.com', 'admin_new_booking', $payload);
        }
    }

    /**
     * Event 2: Booking Confirmation -> Customer (Welcome Credentials if New User)
     */
    public static function notifyBookingConfirmationCustomer(array $booking): void
    {
        $isEnquiry = !empty($booking['is_enquiry']);
        $isNew = !empty($booking['generated_password']) || !empty($booking['is_new_customer']);
        $template = $isEnquiry ? 'customer_enquiry_confirmed' : ($isNew ? 'customer_welcome_booking' : 'booking_confirmation');
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
            'is_enquiry'         => $isEnquiry,
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
     * Event 3 (Lifecycle Email 3): Staff Assignment -> Technician Email with Job Details & 75% Payout
     */
    public static function notifyStaffAssigned(array $booking, array $staff): void
    {
        $jobId = $booking['job_id'] ?? '';
        $baseAmt = (float)($booking['total_amount'] ?? 0);
        if ($baseAmt <= 0) {
            $baseAmt = (float)($booking['starting_price'] ?? 899);
        }

        $serviceId = !empty($booking['service_id']) ? (int)$booking['service_id'] : null;
        $categoryId = !empty($booking['category_id']) ? (int)$booking['category_id'] : null;
        $workerTier = $staff['tier'] ?? 'standard';

        // Compute actual commission split (default 75% worker share) instead of deducting flat platform/advance fees
        $split = \App\Models\CommissionRule::calculateSplit($baseAmt, 0.0, $serviceId, $categoryId, $workerTier);
        $workerPct = (float)($split['worker_percent'] ?? Setting::get('global_worker_commission_pct', 75.0));
        $expectedPayout = (float)($split['worker_total_payout'] ?? $split['worker_share'] ?? round($baseAmt * ($workerPct / 100.0), 2));

        $data = [
            'booking_no'        => $booking['booking_no'] ?? '',
            'staff_name'        => $staff['name'] ?? 'Technician',
            'service_name'      => $booking['service_name'] ?? 'Home Service',
            'preferred_date'    => $booking['preferred_date'] ?? date('Y-m-d'),
            'preferred_time'    => $booking['preferred_time'] ?? 'Flexible',
            'address'           => $booking['address'] ?? '',
            'total_amount'      => $baseAmt,
            'worker_percent'    => $workerPct,
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
     * Event 3B: Admin Assigns Technician -> SMS/WA to Customer (Email channel silenced for 4-email rule)
     */
    public static function notifyTechnicianAssignedCustomer(array $booking, array $staff): void
    {
        $bId = $booking['id'] ?? $booking['booking_id'] ?? '';
        $customerEmail = !empty($booking['email']) ? $booking['email'] : ($booking['customer_email'] ?? null);
        $customerPhone = !empty($booking['phone']) ? $booking['phone'] : ($booking['customer_phone'] ?? '');
        $customerName  = !empty($booking['name']) ? $booking['name'] : ($booking['customer_name'] ?? 'Customer');

        $data = [
            'booking_id'     => $bId,
            'booking_no'     => $booking['booking_no'] ?? '',
            'customer_name'  => $customerName,
            'customer_phone' => $customerPhone,
            'customer_email' => $customerEmail,
            'service_name'   => $booking['service_name'] ?? 'Home Service',
            'staff_name'     => $staff['name'] ?? ($booking['staff_name'] ?? 'Certified Technician'),
            'staff_phone'    => $staff['phone'] ?? ($booking['staff_phone'] ?? ''),
            'preferred_date' => $booking['preferred_date'] ?? date('Y-m-d'),
            'preferred_time' => $booking['preferred_time'] ?? 'Flexible',
            'address'        => $booking['address'] ?? '',
            'booking_url'    => View::fullUrl('/account/bookings/' . $bId),
            'login_url'      => View::fullUrl('/login'),
        ];

        // Email channel intentionally disabled: per business flow, customer receives Booking Confirmation, and final GST Invoice upon completion.
        if (!empty($customerPhone)) {
            self::send('sms', $customerPhone, 'technician_assigned_sms', $data);

            if (Env::get('WHATSAPP_ENABLED', false)) {
                self::send('whatsapp', $customerPhone, 'technician_assigned_wa', $data);
            }
        }
    }

    /**
     * Event 4: Technician Accepts Job (Email silenced to prevent inbox spam)
     */
    public static function notifyTechnicianAccepted(array $booking, array $staff): void
    {
        // Email intentionally silenced to adhere to strict 4-email lifecycle
    }

    /**
     * Event 4B: Technician On The Way -> SMS to Customer (Email silenced)
     */
    public static function notifyTechnicianOnTheWay(array $booking, array $staff): void
    {
        $bId = $booking['id'] ?? $booking['booking_id'] ?? '';
        $customerPhone = !empty($booking['phone']) ? $booking['phone'] : ($booking['customer_phone'] ?? '');
        $customerName  = !empty($booking['name']) ? $booking['name'] : ($booking['customer_name'] ?? 'Customer');

        $data = [
            'booking_id'     => $bId,
            'booking_no'     => $booking['booking_no'] ?? '',
            'customer_name'  => $customerName,
            'customer_phone' => $customerPhone,
            'service_name'   => $booking['service_name'] ?? 'Home Service',
            'staff_name'     => $staff['name'] ?? ($booking['staff_name'] ?? 'Certified Technician'),
            'staff_phone'    => $staff['phone'] ?? ($booking['staff_phone'] ?? ''),
            'preferred_date' => $booking['preferred_date'] ?? date('Y-m-d'),
            'preferred_time' => $booking['preferred_time'] ?? 'Flexible Slot',
            'address'        => $booking['address'] ?? '',
            'booking_url'    => View::fullUrl('/account/bookings/' . $bId),
        ];

        // Email silenced; send SMS arrival notification if phone is present
        if (!empty($customerPhone)) {
            self::send('sms', $customerPhone, 'technician_on_the_way_sms', $data);
        }
    }

    /**
     * Event 5: Prepaid Online Booking (Silenced: customer gets Booking Confirmation email #1 and Final Invoice email #4)
     */
    public static function notifyPrepaidInvoiceWorkDue(array $invoice, array $booking): void
    {
        // Silenced to ensure only 4 lifecycle emails are dispatched
    }

    /**
     * Event 6 (Lifecycle Email 4): Service Completed -> Final GST Tax Invoice to Customer
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

        // 4th Lifecycle Email: Customer Completion & Final GST Tax Invoice
        self::send('email', $customerEmail, 'invoice_final_completed', $data);
    }

    /**
     * Event 7: Booking Status Change -> SMS to Customer (Email silenced)
     */
    public static function notifyBookingStatusChange(array $booking, string $newStatus, ?string $notes = null): void
    {
        $data = [
            'booking_no'    => $booking['booking_no'] ?? '',
            'customer_name' => $booking['name'] ?? ($booking['customer_name'] ?? 'Customer'),
            'status'        => $newStatus,
            'notes'         => $notes ?? '',
        ];

        // Email silenced; send SMS update if phone present
        $phone = $booking['phone'] ?? ($booking['customer_phone'] ?? null);
        if (!empty($phone)) {
            self::send('sms', $phone, 'status_updated_sms', $data);
        }
    }

    /**
     * Event 8: Job Completed (Silenced: Replaced by notifyFinalCompletedInvoice which sends the full GST Tax Invoice to customer)
     */
    public static function notifyJobCompleted(array $booking, ?array $staff = null): void
    {
        // Silenced to ensure only 4 lifecycle emails are dispatched (final invoice is sent via notifyFinalCompletedInvoice)
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
     * Event 13: Partner/Technician Application Received (Under Review)
     */
    public static function notifyPartnerApplicationReceived(array $staffData): void
    {
        if (!empty($staffData['email'])) {
            self::send('email', $staffData['email'], 'partner_application_received', $staffData);
        }
    }

    /**
     * Event 14: Partner/Technician Approved by Admin
     */
    public static function notifyPartnerApproved(array $staffData, ?string $plainPassword = null): void
    {
        if ($plainPassword !== null) {
            $staffData['password'] = $plainPassword;
        }
        if (!empty($staffData['email'])) {
            self::send('email', $staffData['email'], 'partner_approved', $staffData);
        }
    }

    /**
     * Event 14b: Partner/Technician Application Rejected by Admin
     */
    public static function notifyPartnerRejected(array $staffData, string $reason = ''): void
    {
        $staffData['reason'] = $reason;
        if (!empty($staffData['email'])) {
            self::send('email', $staffData['email'], 'partner_rejected', $staffData);
        }
    }

    /**
     * Legacy / Direct Welcome
     */
    public static function notifyPartnerWelcome(array $staffData): void
    {
        if (!empty($staffData['email'])) {
            self::send('email', $staffData['email'], 'partner_welcome', $staffData);
        }
    }

    /**
     * Event 15: New Partner Registration Alert to Admin
     */
    public static function notifyAdminNewPartner(array $staffData): void
    {
        $adminEmail = Setting::get('admin_notification_email') ?: (Setting::get('company_email') ?: 'contact@refixel.com');
        if (!empty($adminEmail)) {
            self::send('email', $adminEmail, 'admin_new_partner', $staffData);
        }
    }

    /**
     * Event 16: Customer Enquiry Email OTP Verification
     */
    public static function notifyEnquiryOtp(string $email, string $otp, array $enquiryData = []): bool
    {
        $data = [
            'otp'     => $otp,
            'email'   => $email,
            'name'    => $enquiryData['name'] ?? 'Customer',
            'service' => $enquiryData['service'] ?? 'Home Maintenance Service',
            'phone'   => $enquiryData['phone'] ?? '',
        ];
        return self::send('email', $email, 'enquiry_email_otp', $data);
    }

    /**
     * Event 17: Admin Provisioned by SuperAdmin -> Send Welcome & Login Credentials
     */
    public static function notifyAdminCreated(array $adminData): bool
    {
        if (empty($adminData['email'])) {
            return false;
        }
        return self::send('email', $adminData['email'], 'admin_welcome_credentials', $adminData);
    }

    /**
     * Event 18: Admin Password Updated by SuperAdmin -> Send Updated Login Credentials
     */
    public static function notifyAdminPasswordReset(array $adminData): bool
    {
        if (empty($adminData['email'])) {
            return false;
        }
        return self::send('email', $adminData['email'], 'admin_password_reset', $adminData);
    }
}
