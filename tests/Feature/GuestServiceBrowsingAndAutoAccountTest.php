<?php
declare(strict_types=1);

require_once __DIR__ . '/../../public/index.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Controllers\BookingController;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Services\SmtpEmailService;

echo "\n=== RUNNING GUEST BROWSING & AUTO-ACCOUNT CREATION TEST SUITE ===\n\n";

$passCount = 0;
$failCount = 0;

function assertTest(bool $condition, string $message): void {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] {$message}\n";
        $passCount++;
    } else {
        echo "  [FAIL] {$message}\n";
        $failCount++;
    }
}

// -------------------------------------------------------------
// [TEST 1] Verify Service Show, Category, and Index Have No Forced Login
// -------------------------------------------------------------
echo "[TEST 1] Verifying Public Service Browsing (Zero Forced Login)...\n";

$showFile = file_get_contents(__DIR__ . '/../../app/views/customer/services/show.php');
assertTest(
    strpos($showFile, "window.location.replace('<?= \\App\\Core\\View::url('/login?redirect=')") === false,
    "show.php has NO forced login script"
);

$homeFile = file_get_contents(__DIR__ . '/../../app/views/customer/home.php');
assertTest(
    strpos($homeFile, "\$cardHref = \$isCustomerLoggedIn ? \$targetUrl : \\App\\Core\\View::url('/login?redirect=") === false,
    "home.php solution cards have NO forced login redirect"
);
assertTest(
    strpos($homeFile, "if (!isCustomerLoggedIn) {\n    document.querySelectorAll('.solution_card") === false,
    "home.php JS has NO guest click interceptor redirecting to /login"
);

$catFile = file_get_contents(__DIR__ . '/../../app/views/customer/services/category.php');
assertTest(
    strpos($catFile, "\$svcUrl = \$isCustomerLoggedIn ? \$targetSvcUrl : \\App\\Core\\View::url('/login?redirect=") === false,
    "category.php services list has NO forced login redirect"
);

$indexFile = file_get_contents(__DIR__ . '/../../app/views/customer/services/index.php');
assertTest(
    strpos($indexFile, "\$svcUrl = \$isCustomerLoggedIn ? \$targetSvcUrl : \\App\\Core\\View::url('/login?redirect=") === false,
    "services/index.php packages list has NO forced login redirect"
);

// -------------------------------------------------------------
// [TEST 2] End-to-End Guest Booking with Auto-Account & Credentials
// -------------------------------------------------------------
echo "\n[TEST 2] Testing First-Time Guest Booking Submission & Auto-Login...\n";

// Ensure session is active and clean
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
Auth::logout();
$_SESSION = [];

$testEmail = 'guest_test_' . time() . '@refixeltest.com';
$testPhone = '98' . str_pad((string)random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);
$testName  = 'Rishabh Customer';

$service = Service::getActive()[0] ?? null;
if (!$service) {
    echo "  [SKIP] No active service found.\n";
} else {
    $req = new Request([], [
        'service_id'     => $service['id'],
        'name'           => $testName,
        'phone'          => $testPhone,
        'email'          => $testEmail,
        'house_no'       => 'Flat 402, Tower B',
        'street'         => 'Mall Road',
        'city'           => 'Kashipur',
        'state'          => 'Uttarakhand',
        'pincode'        => '244713',
        'preferred_date' => date('Y-m-d', strtotime('+1 day')),
        'preferred_time' => '10:00 AM - 12:00 PM',
        'payment_method' => 'cod',
        'is_ajax'        => '1',
    ], [], [], [], ['REQUEST_METHOD' => 'POST']);

    $controller = new BookingController();
    $res = $controller->submit($req);

    $json = json_decode($res->getContent(), true);

    assertTest(!empty($json['success']), "Booking submission succeeded via JSON");
    assertTest(!empty($json['booking_no']), "Booking number generated: " . ($json['booking_no'] ?? 'N/A'));
    assertTest(
        str_contains($json['redirect_url'] ?? '', '/account/bookings'),
        "Redirect target points to customer dashboard (/account/bookings)"
    );

    // Verify User Account was Created
    $createdUser = User::findBy('email', $testEmail);
    assertTest(!empty($createdUser), "New customer user record created in DB");
    assertTest(($createdUser['role'] ?? '') === 'customer', "User role is 'customer'");
    assertTest(($createdUser['status'] ?? '') === 'active', "User status is 'active'");

    // Verify Password was Hashed and matches Generated Password
    $welcomeModal = $_SESSION['new_customer_welcome_modal'] ?? [];
    $plainPass = $welcomeModal['generated_password'] ?? '';
    assertTest(!empty($plainPass), "Generated plain password present in session: {$plainPass}");
    assertTest(str_starts_with($plainPass, 'Refixel@'), "Password follows Refixel@XXXX pattern");
    assertTest(
        password_verify($plainPass, (string)$createdUser['password_hash']),
        "Hashed password in DB verifies against generated plain password"
    );

    // Verify Auto-Login into Session
    assertTest(Auth::check(), "Auth::check() is true (User was auto-logged in)");
    assertTest((int)Auth::id() === (int)$createdUser['id'], "Auth::id() matches new customer ID");

    // Verify Booking Record in DB is Linked to Customer
    $bookingId = (int)($json['booking_id'] ?? 0);
    $savedBooking = Booking::find($bookingId);
    assertTest(!empty($savedBooking), "Booking saved in DB (ID: {$bookingId})");
    assertTest((int)$savedBooking['customer_id'] === (int)$createdUser['id'], "Booking customer_id linked to new user");

    // Verify Booking is visible in Customer Dashboard
    $customerBookings = Booking::findByCustomer((int)$createdUser['id']);
    assertTest(!empty($customerBookings), "Booking is returned by Booking::findByCustomer()");
    assertTest($customerBookings[0]['booking_no'] === $savedBooking['booking_no'], "Customer dashboard sees matching booking #");

    // Verify Booking is visible in Admin Dashboard query
    $adminViewBooking = Booking::findWithDetails($bookingId);
    assertTest(!empty($adminViewBooking), "Booking is found by Booking::findWithDetails() for Admin Dashboard");
    assertTest($adminViewBooking['email'] === $testEmail, "Admin booking query includes customer email");

    // -------------------------------------------------------------
    // [TEST 3] Verify Welcome Email Template Content
    // -------------------------------------------------------------
    echo "\n[TEST 3] Testing Welcome Email Rendering with Generated Password...\n";

    $emailService = new SmtpEmailService();
    $reflection = new \ReflectionClass($emailService);
    $buildBodyMethod = $reflection->getMethod('buildHtmlTemplate');
    $buildBodyMethod->setAccessible(true);

    $renderedHtml = $buildBodyMethod->invoke($emailService, 'customer_welcome_booking', [
        'booking_no'         => $savedBooking['booking_no'],
        'customer_name'      => $testName,
        'service_name'       => $service['name'],
        'identifier'         => $testEmail,
        'generated_password' => $plainPass,
        'address'            => 'Mall Road, Kashipur',
    ]);

    assertTest(str_contains($renderedHtml, $testEmail), "Welcome email contains customer login email");
    assertTest(str_contains($renderedHtml, $plainPass), "Welcome email contains generated password ({$plainPass})");
    assertTest(str_contains($renderedHtml, 'Your REFIXEL Customer Account Credentials'), "Welcome email contains credentials box");

    // -------------------------------------------------------------
    // [TEST 4] Verify Customer Bookings View Contains Welcome Modal
    // -------------------------------------------------------------
    echo "\n[TEST 4] Testing Dashboard Bookings View Renders Modal...\n";

    $bookingsViewFile = file_get_contents(__DIR__ . '/../../app/views/customer/account/bookings.php');
    assertTest(str_contains($bookingsViewFile, 'id="refixelWelcomeModal"'), "bookings.php has refixelWelcomeModal modal");
    assertTest(str_contains($bookingsViewFile, 'wLoginPass'), "bookings.php displays generated password");
    assertTest(str_contains($bookingsViewFile, 'togglePassVisibility'), "bookings.php includes show/hide password toggle");
    assertTest(str_contains($bookingsViewFile, 'copyCred'), "bookings.php includes copy credential button");

    // Cleanup DB Test Records
    Database::execute("DELETE FROM booking_items WHERE booking_id = ?", [$bookingId]);
    Database::execute("DELETE FROM bookings WHERE id = ?", [$bookingId]);
    Database::execute("DELETE FROM customer_profiles WHERE user_id = ?", [$createdUser['id']]);
    Database::execute("DELETE FROM password_resets WHERE email_or_phone = ?", [$testEmail]);
    Database::execute("DELETE FROM users WHERE id = ?", [$createdUser['id']]);

    echo "  [CLEANUP] Test records cleanly purged from database.\n";
}

echo "\n=========================================\n";
echo "TEST RESULTS: {$passCount} PASSED, {$failCount} FAILED\n";
echo "=========================================\n\n";

if ($failCount > 0) {
    exit(1);
}
