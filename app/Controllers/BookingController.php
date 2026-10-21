<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Cart;
use App\Core\Database;
use App\Core\InvoiceBuilder;
use App\Core\Logger;
use App\Core\Notifier;
use App\Core\Request;
use App\Core\Response;
use App\Core\Upload;
use App\Core\View;
use App\Models\Booking;
use App\Models\BookingAttachment;
use App\Models\Service;
use App\Models\ServiceArea;
use App\Models\User;
use App\Services\Payment\RazorpayGateway;
use PDO;

class BookingController extends Controller
{
    public function showForm(Request $request): Response
    {
        $rawCity = $request->query('city');
        if ($rawCity !== null && !ServiceArea::isValidCity((string)$rawCity)) {
            $serviceId = (int)$request->query('service_id', 0);
            $redirectParams = [];
            if ($serviceId > 0) {
                $redirectParams['service_id'] = $serviceId;
            }
            $redirectParams['city'] = ServiceArea::DEFAULT_CITY;
            return $this->redirect('/book?' . http_build_query($redirectParams));
        }

        $serviceId = (int)$request->query('service_id', 0);
        $service = $serviceId ? Service::find($serviceId) : null;
        if ($service && !empty($service['is_coming_soon'])) {
            View::setFlash('warning', "Service '{$service['name']}' is coming soon and bookings will open shortly!");
            return $this->redirect('/' . ($service['slug'] ?? 'services'));
        }
        $allServices = Service::getActive();

        return $this->render('customer.book', [
            'title'       => 'Schedule Doorstep Service | REFIXEL',
            'description' => 'Book verified home and office maintenance services with transparent starting prices and 24-hour guarantee.',
            'service'     => $service,
            'allServices' => $allServices,
        ], 'customer');
    }

    public function cart(Request $request): Response
    {
        $cart = Cart::getDetails();
        $cartServiceIds = array_keys(Cart::getRaw());
        $recommendedAddons = Service::getCartAddonRecommendations($cartServiceIds, 4);

        return $this->render('customer.cart', [
            'title'             => 'Your Service Cart | REFIXEL',
            'description'       => 'Review selected home cleaning and repair packages, calculate prices, and schedule your doorstep visit.',
            'cart'              => $cart,
            'recommendedAddons' => $recommendedAddons,
        ], 'customer');
    }

    public function apiCart(Request $request): Response
    {
        $cart = Cart::getDetails();
        return Response::json([
            'cart_count' => $cart['count'],
            'cart_total' => $cart['total'],
            'cart'       => $cart,
        ]);
    }

    public function apiCartAddons(Request $request): Response
    {
        $serviceId = (int)$request->input('service_id', 0);
        $cartIds = array_keys(Cart::getRaw());

        if ($serviceId > 0) {
            $addons = Service::getAddonRecommendations($serviceId, $cartIds, 4);
        } else {
            $addons = Service::getCartAddonRecommendations($cartIds, 4);
        }

        return Response::json([
            'success' => true,
            'addons'  => $addons,
        ]);
    }

    public function addToCart(Request $request): Response
    {
        // Support both JSON body and standard form post / vendor_price_id
        $serviceId = (int)($request->input('service_id') ?? $request->input('vendor_price_id') ?? 0);
        $qty = (int)($request->input('quantity') ?? 1);

        if ($serviceId <= 0) {
            return Response::json(['success' => false, 'error' => 'Invalid service ID.'], 400);
        }

        $service = Service::find($serviceId);
        if ($service && !empty($service['is_coming_soon'])) {
            return Response::json([
                'success'        => false,
                'status'         => 'error',
                'is_coming_soon' => true,
                'service_name'   => $service['name'],
                'message'        => "Service '{$service['name']}' is coming soon and cannot be added to cart yet.",
                'error'          => "Service '{$service['name']}' is coming soon and cannot be added to cart yet.",
            ], 400);
        }

        $setExact = (bool)($request->input('set_exact', false));
        if ($setExact) {
            $res = Cart::update($serviceId, max(1, $qty));
        } else {
            $res = Cart::add($serviceId, max(1, $qty));
        }

        if (empty($res['success'])) {
            return Response::json([
                'success'        => false,
                'status'         => 'error',
                'is_coming_soon' => !empty($res['is_coming_soon']),
                'error'          => $res['error'] ?? 'Failed to add service to cart.',
                'message'        => $res['error'] ?? 'Failed to add service to cart.',
            ], 400);
        }

        // Support bundling multiple companion add-on services directly
        $addonIds = $request->input('addon_ids');
        if (!empty($addonIds)) {
            if (is_string($addonIds)) {
                $addonIds = array_filter(array_map('intval', explode(',', $addonIds)));
            }
            if (is_array($addonIds)) {
                foreach ($addonIds as $aId) {
                    if ($aId > 0 && $aId !== $serviceId) {
                        Cart::add($aId, 1);
                    }
                }
            }
        }

        $details = Cart::getDetails();

        $addedService = Service::find($serviceId);
        $cartIds = array_keys(Cart::getRaw());
        $addons = Service::getAddonRecommendations($serviceId, $cartIds, 3);

        $addedServiceData = null;
        if ($addedService) {
            $addedServiceData = [
                'id'             => (int)$addedService['id'],
                'name'           => $addedService['name'],
                'slug'           => $addedService['slug'],
                'image'          => Service::resolveImage($addedService['slug'] ?? '', $addedService['image'] ?? null),
                'starting_price' => (float)$addedService['starting_price'],
                'category_name'  => $addedService['category_name'] ?? 'Refixel Service',
            ];
        }

        return Response::json([
            'success'       => $res['success'] ?? true,
            'status'        => ($res['success'] ?? true) ? 'success' : 'error',
            'cart_count'    => $details['count'],
            'cart_total'    => $details['total'],
            'cart'          => $details,
            'added_service' => $addedServiceData,
            'addons'        => $addons,
        ]);
    }

    public function updateCart(Request $request): Response
    {
        $serviceId = (int)($request->input('service_id') ?? $request->input('vendor_price_id') ?? 0);
        $action = (string)$request->input('action', '');
        $qty = (int)($request->input('quantity') ?? 0);

        if ($serviceId <= 0) {
            return Response::json(['success' => false, 'error' => 'Invalid service ID.'], 400);
        }

        $raw = Cart::getRaw();
        $currentQty = $raw[$serviceId] ?? 1;

        if ($action === 'plus') {
            $newQty = $currentQty + 1;
        } elseif ($action === 'minus') {
            $newQty = $currentQty - 1;
        } else {
            $newQty = $qty;
        }

        Cart::update($serviceId, $newQty);
        $details = Cart::getDetails();

        return Response::json([
            'status'     => 'success',
            'cart_count' => $details['count'],
            'cart_total' => $details['total'],
            'cart'       => $details,
        ]);
    }

    public function removeFromCart(Request $request): Response
    {
        $serviceId = (int)$request->input('service_id', 0);
        Cart::remove($serviceId);
        $details = Cart::getDetails();

        return Response::json([
            'status'     => 'success',
            'cart_count' => $details['count'],
            'cart_total' => $details['total'],
            'cart'       => $details,
        ]);
    }

    public function clearCart(Request $request): Response
    {
        Cart::clear();
        return Response::json([
            'status'     => 'success',
            'cart_count' => 0,
            'cart_total' => 0,
        ]);
    }

    /**
     * Parse time slot string to minutes from midnight for slot start time
     */
    public function parseSlotStartMinutes(string $slot): int
    {
        $slot = trim($slot);
        if (preg_match('/^(\d{1,2}):(\d{2})\s*(AM|PM)?/i', $slot, $m)) {
            $hours = (int)$m[1];
            $minutes = (int)$m[2];
            $ampm = !empty($m[3]) ? strtoupper($m[3]) : null;

            if ($ampm === null) {
                if (preg_match('/(AM|PM)$/i', $slot, $endM)) {
                    $endAmpm = strtoupper($endM[1]);
                    if ($endAmpm === 'PM' && $hours < 8) {
                        $hours += 12;
                    }
                }
            } else {
                if ($ampm === 'PM' && $hours < 12) {
                    $hours += 12;
                } elseif ($ampm === 'AM' && $hours === 12) {
                    $hours = 0;
                }
            }
            return $hours * 60 + $minutes;
        }
        return 0;
    }

    /**
     * Self-healing migration for financial tracking columns.
     * Ensures bookings and invoices tables have required columns on legacy/live databases.
     */
    public static function ensureFinancialSchema(): void
    {
        static $executed = false;
        if ($executed) return;
        $executed = true;

        try {
            $bookingCols = Database::fetchAll("SHOW COLUMNS FROM bookings");
            $existingCols = array_column($bookingCols, 'Field');

            if (!in_array('total_amount', $existingCols, true)) {
                try { Database::execute("ALTER TABLE bookings ADD COLUMN total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER issue_details"); } catch (\Throwable $e) {}
            }
            if (!in_array('advance_amount', $existingCols, true)) {
                try { Database::execute("ALTER TABLE bookings ADD COLUMN advance_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00"); } catch (\Throwable $e) {}
            }
            if (!in_array('due_amount', $existingCols, true)) {
                try { Database::execute("ALTER TABLE bookings ADD COLUMN due_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00"); } catch (\Throwable $e) {}
            }
            if (!in_array('payment_type', $existingCols, true)) {
                try { Database::execute("ALTER TABLE bookings ADD COLUMN payment_type VARCHAR(20) NOT NULL DEFAULT 'cod'"); } catch (\Throwable $e) {}
            }
            if (!in_array('items_json', $existingCols, true)) {
                try { Database::execute("ALTER TABLE bookings ADD COLUMN items_json TEXT NULL AFTER issue_details"); } catch (\Throwable $e) {}
            }

            try {
                Database::execute("
                CREATE TABLE IF NOT EXISTS `booking_items` (
                  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                  `booking_id` INT UNSIGNED NOT NULL,
                  `service_id` INT UNSIGNED NULL,
                  `item_type` ENUM('service', 'addon') NOT NULL DEFAULT 'service',
                  `name` VARCHAR(150) NOT NULL,
                  `quantity` INT NOT NULL DEFAULT 1,
                  `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                  `total_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `idx_booking_id` (`booking_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
                ");
            } catch (\Throwable $e) {}

            $invCols = Database::fetchAll("SHOW COLUMNS FROM invoices");
            $existingInvCols = array_column($invCols, 'Field');
            if (!in_array('advance_paid', $existingInvCols, true)) {
                try { Database::execute("ALTER TABLE invoices ADD COLUMN advance_paid DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER total"); } catch (\Throwable $e) {}
            }
            if (!in_array('due_amount', $existingInvCols, true)) {
                try { Database::execute("ALTER TABLE invoices ADD COLUMN due_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER advance_paid"); } catch (\Throwable $e) {}
            }
        } catch (\Throwable $e) {
            Logger::warning("Schema verification skipped: " . $e->getMessage());
        }
    }

    /**
     * Safely updates booking financial tracking columns.
     * Tries full financial column update first; falls back gracefully to priority/status
     * if the database cannot be altered.
     */
    public function updateBookingFinancials(int $bookingId, float $totalAmount, float $advanceAmount, float $dueAmount, string $paymentMethod, string $priority, ?string $status = null): void
    {
        self::ensureFinancialSchema();

        try {
            if ($status !== null) {
                Database::execute(
                    "UPDATE bookings SET priority = ?, status = ?, total_amount = ?, advance_amount = ?, due_amount = ?, payment_type = ? WHERE id = ?",
                    [$priority, $status, $totalAmount, $advanceAmount, $dueAmount, $paymentMethod, $bookingId]
                );
            } else {
                Database::execute(
                    "UPDATE bookings SET total_amount = ?, advance_amount = ?, due_amount = ?, payment_type = ?, priority = ? WHERE id = ?",
                    [$totalAmount, $advanceAmount, $dueAmount, $paymentMethod, $priority, $bookingId]
                );
            }
            return;
        } catch (\Throwable $updErr) {
            Logger::warning("Could not update financial columns on bookings, falling back to basic columns: " . $updErr->getMessage());
        }

        // Safe fallback for live database if columns do not exist and could not be altered
        try {
            if ($status !== null) {
                Database::execute("UPDATE bookings SET priority = ?, status = ? WHERE id = ?", [$priority, $status, $bookingId]);
            } else {
                Database::execute("UPDATE bookings SET priority = ? WHERE id = ?", [$priority, $bookingId]);
            }
        } catch (\Throwable $e) {
            Logger::error("Failed updating booking priority/status: " . $e->getMessage());
        }
    }

    public function submit(Request $request): Response
    {
        try {
            if ($request->input('from_cart') && (int)$request->input('service_id', 0) <= 0) {
                $cartDetails = Cart::getDetails();
                if (!empty($cartDetails['items'][0]['service_id'])) {
                    $request->set('service_id', (int)$cartDetails['items'][0]['service_id']);
                }
            }

            $addrInput = trim((string)$request->input('address', ''));
            if (strlen($addrInput) < 5) {
                $synthParts = array_filter([
                    trim((string)$request->input('house_no')) ?: 'House No. 1',
                    trim((string)$request->input('street')),
                    trim((string)$request->input('city')),
                    trim((string)$request->input('state')),
                    trim((string)$request->input('pincode')),
                ]);
                $composedAddr = implode(', ', $synthParts);
                $request->set('address', strlen($composedAddr) >= 5 ? $composedAddr : 'Doorstep Service Location, Uttarakhand');
            }

            $validator = $this->validate($request, [
                'service_id'     => 'required|numeric',
                'name'           => 'required|min:2',
                'phone'          => 'required|phone',
                'address'        => 'required|min:5',
                'preferred_date' => 'required',
                'preferred_time' => 'required',
            ]);

            if ($validator->fails()) {
                if ($request->isAjax() || $request->input('is_ajax')) {
                    return Response::json([
                        'success' => false,
                        'message' => $validator->firstError(),
                        'error'   => $validator->firstError(),
                    ], 422);
                }
                View::setFlash('error', $validator->firstError());
                $redirectUrl = $request->input('from_cart') ? '/cart' : '/book?service_id=' . (int)$request->input('service_id');
                return $this->redirect($redirectUrl);
            }

            $serviceId = (int)$request->input('service_id');
            $service = Service::find($serviceId);
            if (!$service || empty($service['is_active'])) {
                if ($request->isAjax() || $request->input('is_ajax')) {
                    return Response::json([
                        'success' => false,
                        'message' => 'The requested service is currently unavailable.',
                        'error'   => 'The requested service is currently unavailable.',
                    ], 400);
                }
                View::setFlash('error', 'The requested service is currently unavailable.');
                return $this->redirect('/services');
            }

            if (!empty($service['is_coming_soon'])) {
                if ($request->isAjax() || $request->input('is_ajax')) {
                    return Response::json([
                        'success'        => false,
                        'is_coming_soon' => true,
                        'message'        => "Service '{$service['name']}' is coming soon and cannot be booked yet.",
                        'error'          => "Service '{$service['name']}' is coming soon and cannot be booked yet.",
                    ], 400);
                }
                View::setFlash('error', "Service '{$service['name']}' is coming soon and cannot be booked yet.");
                return $this->redirect('/' . ($service['slug'] ?? 'services'));
            }

            if ($request->input('from_cart')) {
                $cartItems = Cart::getDetails()['items'] ?? [];
                foreach ($cartItems as $cItem) {
                    $cSvc = Service::find((int)$cItem['service_id']);
                    if ($cSvc && !empty($cSvc['is_coming_soon'])) {
                        if ($request->isAjax() || $request->input('is_ajax')) {
                            return Response::json([
                                'success'        => false,
                                'is_coming_soon' => true,
                                'message'        => "Service '{$cSvc['name']}' in your cart is coming soon and cannot be booked yet.",
                                'error'          => "Service '{$cSvc['name']}' in your cart is coming soon and cannot be booked yet.",
                            ], 400);
                        }
                        View::setFlash('error', "Service '{$cSvc['name']}' in your cart is coming soon and cannot be booked yet.");
                        return $this->redirect('/cart');
                    }
                }
            }

            // Validate Preferred Date & Time Slot (30 mins advance booking requirement for today)
            $prefDate = trim((string)$request->input('preferred_date', ''));
            $prefTime = trim((string)$request->input('preferred_time', ''));
            $todayYmd = date('Y-m-d');

            if ($prefDate < $todayYmd) {
                $dateErr = 'Please select today or an upcoming date for your service.';
                if ($request->isAjax() || $request->input('is_ajax')) {
                    return Response::json(['success' => false, 'message' => $dateErr, 'error' => $dateErr], 422);
                }
                View::setFlash('error', $dateErr);
                return $this->redirect('/book?service_id=' . $serviceId);
            }

            if ($prefDate === $todayYmd) {
                $slotStartMinutes = $this->parseSlotStartMinutes($prefTime);
                $currentMinutes = (int)date('H') * 60 + (int)date('i');
                $cutoffMinutes = $currentMinutes + 30; // 30 minutes in advance required

                if ($slotStartMinutes > 0 && $slotStartMinutes < $cutoffMinutes) {
                    $slotErr = "The selected time slot ({$prefTime}) has passed or is within 30 minutes. Please select an upcoming slot starting at least 30 minutes from now or pick another date.";
                    if ($request->isAjax() || $request->input('is_ajax')) {
                        return Response::json(['success' => false, 'message' => $slotErr, 'error' => $slotErr], 422);
                    }
                    View::setFlash('error', $slotErr);
                    return $this->redirect('/book?service_id=' . $serviceId);
                }
            }

            // Pincode validation (optional but verified if supplied)
            $pincode = $request->input('pincode') ? trim((string)$request->input('pincode')) : null;
            if ($pincode && !preg_match('/^[0-9]{6}$/', $pincode)) {
                if ($request->isAjax() || $request->input('is_ajax')) {
                    return Response::json([
                        'success' => false,
                        'message' => 'Please enter a valid 6-digit Indian postal pincode.',
                        'error'   => 'Please enter a valid 6-digit Indian postal pincode.',
                    ], 422);
                }
                View::setFlash('error', 'Please enter a valid 6-digit Indian postal pincode.');
                return $this->redirect('/book?service_id=' . $serviceId);
            }

            $bookingNo = Booking::generateBookingNo();
            $customerId = Auth::id(); // Null for guest, or user ID if authenticated
            if ($customerId) {
                $userInDb = \App\Models\User::find($customerId);
                if (!$userInDb) {
                    // Stale or deleted user in session - clear it so foreign key constraints never fail
                    $customerId = null;
                    if (isset($_SESSION['user'])) {
                        unset($_SESSION['user']);
                    }
                }
            }
            $phoneInput = preg_replace('/\D/', '', (string)$request->input('phone'));
            $emailInput = $request->input('email') ? trim((string)$request->input('email')) : null;
            if (empty($emailInput)) {
                $currAuth = \App\Core\Auth::user();
                if ($currAuth && !empty($currAuth['email'])) {
                    $emailInput = trim((string)$currAuth['email']);
                } elseif (!empty($phoneInput)) {
                    $uByPhone = \App\Models\User::findByPhone($phoneInput);
                    if ($uByPhone && !empty($uByPhone['email'])) {
                        $emailInput = trim((string)$uByPhone['email']);
                    }
                }
            }
            $nameInput = trim((string)$request->input('name'));
            
            $priority = in_array($request->input('priority'), ['high', 'urgent']) ? (string)$request->input('priority') : 'normal';

            $addressInput = trim((string)$request->input('address'));
            $cityInput = ServiceArea::normalizeCity((string)$request->input('city'));
            $defaultState = ($cityInput === 'Thakurdwara') ? 'Uttar Pradesh' : 'Uttarakhand';
            $stateInput = trim((string)$request->input('state', $defaultState)) ?: $defaultState;
            $houseNoInput = trim((string)$request->input('house_no'));
            if (empty($houseNoInput)) {
                $houseNoInput = 'House No. 1';
            }
            $streetInput = trim((string)$request->input('street'));
            $addressTypeInput = trim((string)$request->input('address_type', 'Home'));

            // Cryptographically secure unpredictable random initial password - NEVER printed, logged, or shared
            $randomSecret = bin2hex(random_bytes(16));
            $hashedPassword = password_hash($randomSecret, PASSWORD_BCRYPT);
            $setPasswordUrl = null;

            // Checkout authentication & account preparation
            if (!$customerId) {
                $existingUser = null;
                if (!empty($phoneInput)) {
                    $existingUser = \App\Models\User::findByPhone($phoneInput);
                }
                if (!$existingUser && !empty($emailInput)) {
                    $existingUser = \App\Models\User::findBy('email', $emailInput);
                }
                
                if ($existingUser) {
                    $customerId = (int)$existingUser['id'];
                    $_SESSION['guest_account_created'] = false;
                    $_SESSION['guest_account_identifier'] = !empty($existingUser['phone']) ? $existingUser['phone'] : ($existingUser['email'] ?? '');
                } else {
                    try {
                        $customerId = (int)\App\Core\Database::execute(
                            "INSERT INTO users (name, phone, email, password_hash, role, status) VALUES (?, ?, ?, ?, 'customer', 'active')",
                            [$nameInput, !empty($phoneInput) ? $phoneInput : null, !empty($emailInput) ? $emailInput : null, $hashedPassword],
                            true // Return last insert ID
                        );
                    } catch (\Throwable $e) {
                        // Fallback lookup if duplicate was hit
                        $existingUser = null;
                        if (!empty($phoneInput)) {
                            $existingUser = \App\Models\User::findByPhone($phoneInput);
                        }
                        if (!$existingUser && !empty($emailInput)) {
                            $existingUser = \App\Models\User::findBy('email', $emailInput);
                        }
                        if ($existingUser) {
                            $customerId = (int)$existingUser['id'];
                        } else {
                            throw $e;
                        }
                    }

                    $_SESSION['guest_account_created'] = true;
                    $_SESSION['guest_account_identifier'] = !empty($phoneInput) ? $phoneInput : ($emailInput ?: '');

                    // Generate secure 64-char single-use token for setting password (valid 24h)
                    $rawSetPassToken = bin2hex(random_bytes(32));
                    $tokenHash = hash('sha256', $rawSetPassToken);
                    $tokenExpiresAt = date('Y-m-d H:i:s', time() + (24 * 3600));
                    $userIdentifier = !empty($emailInput) ? $emailInput : (!empty($phoneInput) ? $phoneInput : (string)$customerId);

                    \App\Core\Database::execute(
                        "INSERT INTO password_resets (email_or_phone, token_hash, expires_at) VALUES (:ident, :hash, :expires)",
                        ['ident' => $userIdentifier, 'hash' => $tokenHash, 'expires' => $tokenExpiresAt]
                    );
                    $setPasswordUrl = \App\Core\View::fullUrl('/reset-password?token=' . $rawSetPassToken);

                    $_SESSION['guest_set_password_url'] = $setPasswordUrl;
                    unset($_SESSION['guest_account_password']);

                    // If user does not have email, dispatch set-password link via SMS
                    if (empty($emailInput) && !empty($phoneInput)) {
                        try {
                            Notifier::send('sms', $phoneInput, 'set_password_sms', [
                                'set_password_url' => $setPasswordUrl,
                                'name'             => $nameInput,
                            ]);
                        } catch (\Throwable $smsErr) {
                            Logger::warning("Set-password SMS notification skipped: " . $smsErr->getMessage());
                        }
                    }
                }
                // Note: Guest users are NOT auto-logged in. Access is strictly controlled via guest_token.
            } else {
                $_SESSION['guest_account_created'] = false;
                unset($_SESSION['guest_set_password_url'], $_SESSION['guest_account_password']);
            }

            if ($customerId) {
                $verifiedUser = \App\Models\User::find($customerId);
                if ($verifiedUser) {
                    try {
                        $existingProfile = \App\Core\Database::fetchOne("SELECT id FROM customer_profiles WHERE user_id = ?", [$customerId]);
                        if ($existingProfile) {
                            \App\Core\Database::execute("UPDATE customer_profiles SET address = ?, pincode = ?, city = ?, state = ?, house_no = ?, street = ?, address_type = ? WHERE user_id = ?", [$addressInput, $pincode, $cityInput, $stateInput, $houseNoInput, $streetInput, $addressTypeInput, $customerId]);
                        } else {
                            \App\Core\Database::execute("INSERT INTO customer_profiles (user_id, address, pincode, city, state, house_no, street, address_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [$customerId, $addressInput, $pincode, $cityInput, $stateInput, $houseNoInput, $streetInput, $addressTypeInput]);
                        }
                    } catch (\Throwable $profErr) {
                        Logger::warning("Customer profile save skipped: " . $profErr->getMessage());
                    }

                    // Sync user name and email in users table if provided
                    if (!empty($nameInput) || !empty($emailInput)) {
                        try {
                            $userUpdates = [];
                            $userParams = [];
                            if (!empty($nameInput)) {
                                $userUpdates[] = "name = ?";
                                $userParams[] = $nameInput;
                            }
                            if (!empty($emailInput)) {
                                $emailOwner = \App\Models\User::findBy('email', $emailInput);
                                if (!$emailOwner || (int)$emailOwner['id'] === (int)$customerId) {
                                    $userUpdates[] = "email = ?";
                                    $userParams[] = $emailInput;
                                }
                            }
                            if (!empty($userUpdates)) {
                                $userParams[] = $customerId;
                                \App\Core\Database::execute("UPDATE users SET " . implode(', ', $userUpdates) . " WHERE id = ?", $userParams);
                            }

                            $currUser = Auth::user();
                            if ($currUser && (int)$currUser['id'] === (int)$customerId) {
                                if (!empty($nameInput)) $currUser['name'] = $nameInput;
                                if (!empty($emailInput) && (!isset($emailOwner) || !$emailOwner || (int)$emailOwner['id'] === (int)$customerId)) {
                                    $currUser['email'] = $emailInput;
                                }
                                Auth::login($currUser);
                            }
                        } catch (\Throwable $userSyncErr) {
                            Logger::warning("Non-fatal: user name/email sync skipped: " . $userSyncErr->getMessage());
                        }
                    }
                }
            }

            $validCustomerId = ($customerId && \App\Models\User::find($customerId)) ? $customerId : null;

            $paymentMethodInput = (string)$request->input('payment_method', 'cod');
            $paymentMethod = ($paymentMethodInput === 'online') ? 'online' : 'cod';
            $priority = (string)$request->input('priority', ($paymentMethod === 'online') ? 'high' : 'normal');
            if ($paymentMethod === 'online') {
                $priority = 'high';
            }

            $guestToken = Booking::generateGuestToken();

            $bookingId = Booking::create([
                'booking_no'     => $bookingNo,
                'guest_token'    => $guestToken,
                'customer_id'    => $validCustomerId,
                'service_id'     => $service['id'],
                'name'           => trim((string)$request->input('name')),
                'phone'          => preg_replace('/\D/', '', (string)$request->input('phone')),
                'email'          => $request->input('email') ? trim((string)$request->input('email')) : null,
                'address'        => trim((string)$request->input('address')),
                'pincode'        => $pincode,
                'preferred_date' => $prefDate,
                'preferred_time' => $prefTime,
                'issue_details'  => $request->input('issue_details') ? trim((string)$request->input('issue_details')) : null,
                'status'         => 'new',
                'priority'       => $priority,
            ]);

            $_SESSION['guest_account_created_booking_id'] = $bookingId;

            // Process issue photo attachments securely if uploaded
            $files = $_FILES['photos'] ?? null;
            if ($files && is_array($files['name'])) {
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        try {
                            $singleFile = [
                                'name'     => $files['name'][$i],
                                'type'     => $files['type'][$i],
                                'tmp_name' => $files['tmp_name'][$i],
                                'error'    => $files['error'][$i],
                                'size'     => $files['size'][$i],
                            ];
                            $uploadResult = Upload::processWithMetadata($singleFile, 'issues');
                            BookingAttachment::create([
                                'booking_id' => $bookingId,
                                'file_path'  => $uploadResult['file_path'],
                                'mime'       => $uploadResult['mime'],
                                'size'       => $uploadResult['size'],
                            ]);
                        } catch (\Throwable $e) {
                            error_log("Issue photo upload skipped: " . $e->getMessage());
                        }
                    }
                }
            }

            // Record DPDP Act consent for booking processing
            if ($validCustomerId) {
                try {
                    \App\Models\Consent::record($validCustomerId, 'booking_terms_and_data_processing', $request->getIp());
                } catch (\Throwable $consentErr) {
                    Logger::warning("Consent record skipped: " . $consentErr->getMessage());
                }
            }

            // Auto-assign to available technician if Admin Busy Mode is active
            if (\App\Services\AutoDispatchService::isBusyModeEnabled()) {
                try {
                    \App\Services\AutoDispatchService::handleNewBooking((int)$bookingId);
                } catch (\Throwable $autoDispatchErr) {
                    Logger::error("AutoDispatch error for booking #{$bookingId}: " . $autoDispatchErr->getMessage());
                }
            }

            // Record payment record in payments table
            $paymentMethodInput = (string)$request->input('payment_method', 'cod');
            $paymentMethod = ($paymentMethodInput === 'online') ? 'online' : 'cod';
            
            $totalServiceAmount = (float)($service['starting_price'] ?? 0);
            $itemsToRecord = [];

            if ($request->input('from_cart')) {
                $cartData = Cart::getDetails();
                if (!empty($cartData['total'])) {
                    $totalServiceAmount = (float)$cartData['total'];
                }
                if (!empty($cartData['items'])) {
                    $isFirst = true;
                    foreach ($cartData['items'] as $cItem) {
                        $isMain = ($isFirst && (int)$cItem['service_id'] === (int)$service['id']);
                        $itemsToRecord[] = [
                            'service_id'  => (int)$cItem['service_id'],
                            'name'        => (string)$cItem['name'],
                            'item_type'   => $isMain ? 'service' : 'addon',
                            'quantity'    => max(1, (int)($cItem['quantity'] ?? 1)),
                            'unit_price'  => (float)($cItem['unit_price'] ?? 0),
                            'total_price' => (float)($cItem['line_total'] ?? 0),
                        ];
                        $isFirst = false;
                    }
                }
                // Clear cart only AFTER obtaining details
                Cart::clear();
            }

            // Direct add-on IDs passed in booking request
            $directAddonIds = $request->input('addon_ids');
            if (!empty($directAddonIds)) {
                if (is_string($directAddonIds)) {
                    $directAddonIds = array_filter(array_map('intval', explode(',', $directAddonIds)));
                }
                if (is_array($directAddonIds)) {
                    foreach ($directAddonIds as $aId) {
                        if ($aId > 0 && $aId !== (int)$service['id']) {
                            $addonSvc = Service::find($aId);
                            if ($addonSvc) {
                                $aPrice = (float)($addonSvc['starting_price'] ?? 0);
                                $totalServiceAmount += $aPrice;
                                $itemsToRecord[] = [
                                    'service_id'  => (int)$addonSvc['id'],
                                    'name'        => (string)$addonSvc['name'],
                                    'item_type'   => 'addon',
                                    'quantity'    => 1,
                                    'unit_price'  => $aPrice,
                                    'total_price' => $aPrice,
                                ];
                            }
                        }
                    }
                }
            }

            // If no items were constructed, record default primary service
            if (empty($itemsToRecord)) {
                $itemsToRecord[] = [
                    'service_id'  => (int)$service['id'],
                    'name'        => (string)$service['name'],
                    'item_type'   => 'service',
                    'quantity'    => 1,
                    'unit_price'  => $totalServiceAmount,
                    'total_price' => $totalServiceAmount,
                ];
            }

            // Save line items to booking_items & update items_json
            Booking::recordItems($bookingId, $itemsToRecord);

            if ($totalServiceAmount <= 0) {
                $totalServiceAmount = 899.00;
            }

            // COD requires ₹49 pre-booking confirmation online, remaining is due after service
            $advanceAmount = ($paymentMethod === 'online') ? $totalServiceAmount : 49.00;
            $dueAmount = max(0.00, round($totalServiceAmount - $advanceAmount, 2));
            if ($request->input('priority') !== null && $request->input('priority') !== '') {
                $priority = (string)$request->input('priority');
            } else {
                $priority = ($paymentMethod === 'online') ? 'high' : 'normal';
            }

            // Update booking financial tracking columns (self-healing & backward-compatible)
            $this->updateBookingFinancials($bookingId, $totalServiceAmount, $advanceAmount, $dueAmount, $paymentMethod, $priority);

            $rzpOrder = null;
            try {
                $rzpGateway = new RazorpayGateway();
                $rzpOrder = $rzpGateway->createOrder($bookingId, $advanceAmount, 'INR', [
                    'booking_no'     => $bookingNo,
                    'service_name'   => $service['name'] ?? 'Doorstep Service',
                    'customer_id'    => (string)($validCustomerId ?? ''),
                    'payment_type'   => $paymentMethod,
                    'total_amount'   => (string)$totalServiceAmount,
                    'advance_amount' => (string)$advanceAmount,
                    'due_amount'     => (string)$dueAmount,
                ]);

                $orderId = $rzpOrder['order_id'] ?? null;
                if (!empty($orderId)) {
                    \App\Core\Database::getInstance()->query(
                        "UPDATE bookings SET gateway_order_id = ? WHERE id = ?",
                        [$orderId, $bookingId]
                    );

                    \App\Models\Payment::create([
                        'booking_id'       => $bookingId,
                        'amount'           => $advanceAmount,
                        'method'           => 'upi',
                        'status'           => 'pending',
                        'gateway_order_id' => $orderId,
                        'transaction_ref'  => $orderId,
                        'paid_at'          => null,
                        'recorded_by'      => $validCustomerId ?: null,
                    ]);
                }
            } catch (\Throwable $e) {
                Logger::error("Razorpay order creation error for booking #{$bookingId}: " . $e->getMessage());
            }

            if ($request->isAjax() || $request->input('is_ajax')) {
                $isNewAcc = !empty($_SESSION['guest_account_created']);
                $ident = $_SESSION['guest_account_identifier'] ?? $phoneInput;

                if (!empty($rzpOrder['order_id'])) {
                    $rzpGateway = new RazorpayGateway();
                    return Response::json([
                        'success'            => true,
                        'requires_payment'   => true,
                        'razorpay_order_id'  => $rzpOrder['order_id'],
                        'razorpay_key_id'    => $rzpGateway->getKeyId(),
                        'amount'             => (int)round($advanceAmount * 100), // paise for Razorpay
                        'currency'           => 'INR',
                        'booking_no'         => $bookingNo,
                        'booking_id'         => $bookingId,
                        'name'               => $nameInput,
                        'phone'              => $phoneInput,
                        'email'              => $emailInput ?? '',
                        'service_name'       => $service['name'] ?? 'Doorstep Service',
                        'address'            => $addressInput,
                        'city'               => $cityInput,
                        'preferred_date'     => $prefDate,
                        'preferred_time'     => $prefTime,
                        'payment_type'       => $paymentMethod,
                        'total_amount'       => $totalServiceAmount,
                        'advance_amount'     => $advanceAmount,
                        'due_amount'         => $dueAmount,
                        'priority'           => $priority,
                        'priority_label'     => ($priority === 'high') ? 'High Priority' : 'Medium Priority',
                        'is_new_customer'    => $isNewAcc,
                        'set_password_url'   => $setPasswordUrl ?? null,
                        'identifier'         => $ident,
                        'redirect_url'       => View::url('/book-success?token=' . urlencode($guestToken)),
                        'guest_token'        => $guestToken,
                    ]);
                }

                // If direct booking without online payment requirement, dispatch notifications:
                try {
                    $directRecord = [
                        'id'               => $bookingId,
                        'booking_id'       => $bookingId,
                        'customer_id'      => $validCustomerId ?? ($customerId ?? null),
                        'booking_no'       => $bookingNo,
                        'service_name'     => $service['name'] ?? 'Doorstep Service',
                        'name'             => $nameInput,
                        'phone'            => $phoneInput,
                        'email'            => $emailInput ?? null,
                        'address'          => $addressInput,
                        'city'             => $cityInput,
                        'preferred_date'   => $prefDate,
                        'preferred_time'   => $prefTime,
                        'total_amount'     => $totalServiceAmount,
                        'advance_amount'   => 0.00,
                        'due_amount'       => $totalServiceAmount,
                        'payment_type'     => 'cod',
                        'priority'         => $priority,
                        'is_new_customer'  => $isNewAcc,
                        'login_url'        => View::fullUrl('/login'),
                        'admin_url'        => View::fullUrl('/admin/bookings/' . $bookingId),
                        'set_password_url' => $setPasswordUrl ?? null,
                        'guest_token'      => $guestToken,
                    ];
                    Notifier::notifyNewBookingAdmin($directRecord);
                    Notifier::notifyBookingConfirmationCustomer($directRecord);
                } catch (\Throwable $ne) {
                    Logger::error("Direct booking notification error: " . $ne->getMessage());
                }

                return Response::json([
                    'success'            => true,
                    'requires_payment'   => false,
                    'booking_no'         => $bookingNo,
                    'booking_id'         => $bookingId,
                    'name'               => $nameInput,
                    'phone'              => $phoneInput,
                    'service_name'       => $service['name'] ?? 'Doorstep Service',
                    'address'            => $addressInput,
                    'city'               => $cityInput,
                    'preferred_date'     => $prefDate,
                    'preferred_time'     => $prefTime,
                    'payment_method'     => 'Pay After Service',
                    'is_new_customer'    => $isNewAcc,
                    'set_password_url'   => $setPasswordUrl ?? null,
                    'identifier'         => $ident,
                    'redirect_url'       => View::url('/book-success?token=' . urlencode($guestToken)),
                    'guest_token'        => $guestToken,
                ]);
            }

            // Direct non-AJAX booking fallback notification:
            try {
                $directRecord = [
                    'id'               => $bookingId,
                    'booking_id'       => $bookingId,
                    'customer_id'      => $validCustomerId ?? ($customerId ?? null),
                    'booking_no'       => $bookingNo,
                    'service_name'     => $service['name'] ?? 'Doorstep Service',
                    'name'             => $nameInput,
                    'phone'            => $phoneInput,
                    'email'            => $emailInput ?? null,
                    'address'          => $addressInput,
                    'city'             => $cityInput,
                    'preferred_date'   => $prefDate,
                    'preferred_time'   => $prefTime,
                    'total_amount'     => $totalServiceAmount,
                    'advance_amount'   => 0.00,
                    'due_amount'       => $totalServiceAmount,
                    'payment_type'     => 'cod',
                    'priority'         => $priority,
                    'is_new_customer'  => !empty($_SESSION['guest_account_created']),
                    'login_url'        => View::fullUrl('/login'),
                    'admin_url'        => View::fullUrl('/admin/bookings/' . $bookingId),
                    'set_password_url' => $setPasswordUrl ?? null,
                    'guest_token'      => $guestToken,
                ];
                Notifier::notifyNewBookingAdmin($directRecord);
                Notifier::notifyBookingConfirmationCustomer($directRecord);
            } catch (\Throwable $ne) {}

            return $this->redirect('/book-success?token=' . urlencode($guestToken));
        } catch (\Throwable $e) {
            Logger::error("Booking submission exception: " . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($request->isAjax() || $request->input('is_ajax')) {
                return Response::json([
                    'success' => false,
                    'message' => 'Unable to complete booking: ' . $e->getMessage(),
                    'error'   => 'Unable to complete booking: ' . $e->getMessage(),
                ], 500);
            }

            View::setFlash('error', 'Unable to complete booking: ' . $e->getMessage());
            $redirectServiceId = (int)$request->input('service_id', 0);
            return $this->redirect($redirectServiceId > 0 ? '/book?service_id=' . $redirectServiceId : '/services');
        }
    }

    public function verifyPayment(Request $request): Response
    {
        $bookingId = (int)$request->input('booking_id');
        $bookingNo = (string)$request->input('booking_no');
        $orderId = trim((string)$request->input('razorpay_order_id'));
        $paymentId = trim((string)$request->input('razorpay_payment_id'));
        $signature = trim((string)$request->input('razorpay_signature'));

        if (empty($bookingId) || empty($orderId) || empty($paymentId) || empty($signature)) {
            return Response::json([
                'success' => false,
                'message' => 'Incomplete payment verification payload.',
            ], 400);
        }

        $booking = Booking::find($bookingId);
        if (!$booking) {
            return Response::json([
                'success' => false,
                'message' => 'Booking not found for payment confirmation.',
            ], 404);
        }

        // Ownership Check: Validate current authenticated user or matching guest_token
        $isOwner = false;
        $currentUserId = \App\Core\Auth::id();
        if ($currentUserId && !empty($booking['customer_id']) && (int)$booking['customer_id'] === (int)$currentUserId) {
            $isOwner = true;
        }

        $providedGuestToken = trim((string)$request->input('guest_token', ''));
        if (!empty($booking['guest_token']) && !empty($providedGuestToken)) {
            if (hash_equals((string)$booking['guest_token'], $providedGuestToken)) {
                $isOwner = true;
            }
        }

        if (!$isOwner && !empty($_SESSION['guest_account_created_booking_id']) && (int)$_SESSION['guest_account_created_booking_id'] === $bookingId) {
            $isOwner = true;
        }

        if (!$isOwner) {
            Logger::warning("Unauthorized payment verification attempt for booking #{$bookingId}");
            return Response::json([
                'success' => false,
                'message' => 'Unauthorized payment verification access.',
            ], 403);
        }

        // Validate gateway_order_id match with booking record if stored
        if (!empty($booking['gateway_order_id']) && !hash_equals((string)$booking['gateway_order_id'], $orderId)) {
            Logger::warning("Booking gateway_order_id mismatch for booking #{$bookingId}: expected {$booking['gateway_order_id']}, got {$orderId}");
            return Response::json([
                'success' => false,
                'message' => 'Payment order mismatch with booking record.',
            ], 400);
        }

        // HMAC SHA256 Signature Verification
        $rzpGateway = new RazorpayGateway();
        $isValid = $rzpGateway->verifySignature([
            'razorpay_order_id'   => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature'  => $signature,
        ]);

        if (!$isValid) {
            Logger::error("Razorpay signature verification failed for booking #{$bookingId}: order={$orderId}, payment={$paymentId}");
            return Response::json([
                'success' => false,
                'message' => 'Payment signature verification failed. Please try again or contact support.',
            ], 400);
        }

        // Amount Calculation & Verification
        $totalAmount = (float)($booking['total_amount'] ?? 0);
        if ($totalAmount <= 0) {
            $totalAmount = 899.00;
        }
        $paymentType = (string)($booking['payment_type'] ?? 'cod');
        $advanceAmount = ($paymentType === 'online') ? $totalAmount : 49.00;
        if ((float)($booking['advance_amount'] ?? 0) > 0) {
            $advanceAmount = (float)$booking['advance_amount'];
        }
        $dueAmount = max(0.00, round($totalAmount - $advanceAmount, 2));

        // Enforce server-side pending payment amount matching if pending record exists
        $pendingPayment = Database::fetchOne(
            "SELECT * FROM payments WHERE booking_id = ? AND (gateway_order_id = ? OR gateway_order_id IS NULL) ORDER BY id DESC LIMIT 1",
            [$bookingId, $orderId]
        );
        if ($pendingPayment && (float)$pendingPayment['amount'] > 0) {
            $expectedAmount = (float)$pendingPayment['amount'];
            if (abs($expectedAmount - $advanceAmount) > 0.01) {
                Logger::error("Payment amount mismatch for booking #{$bookingId}: expected {$expectedAmount}, calculated {$advanceAmount}");
                return Response::json([
                    'success' => false,
                    'message' => 'Payment amount verification mismatch.',
                ], 400);
            }
        }

        // Atomic Payment State Transition
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        $isAlreadyPaid = false;
        $paymentIdDb = 0;

        try {
            $stmt = $pdo->prepare("SELECT id, status, amount FROM payments WHERE booking_id = ? AND (gateway_order_id = ? OR gateway_order_id IS NULL) ORDER BY id DESC LIMIT 1 FOR UPDATE");
            $stmt->execute([$bookingId, $orderId]);
            $paymentRow = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($paymentRow && $paymentRow['status'] === 'paid') {
                $isAlreadyPaid = true;
                $paymentIdDb = (int)$paymentRow['id'];
            } elseif ($paymentRow) {
                $updStmt = $pdo->prepare(
                    "UPDATE payments SET status = 'paid', method = 'upi', amount = ?, gateway_order_id = ?, gateway_payment_id = ?, transaction_ref = ?, paid_at = NOW() WHERE id = ? AND status = 'pending'"
                );
                $updStmt->execute([$advanceAmount, $orderId, $paymentId, $paymentId, $paymentRow['id']]);
                $affected = $updStmt->rowCount();
                $paymentIdDb = (int)$paymentRow['id'];
                if ($affected === 0) {
                    $isAlreadyPaid = true;
                }
            } else {
                $insStmt = $pdo->prepare(
                    "INSERT INTO payments (booking_id, amount, method, status, gateway_order_id, gateway_payment_id, transaction_ref, paid_at, recorded_by) VALUES (?, ?, 'upi', 'paid', ?, ?, ?, NOW(), ?)"
                );
                $insStmt->execute([$bookingId, $advanceAmount, $orderId, $paymentId, $paymentId, $booking['customer_id'] ?: null]);
                $paymentIdDb = (int)$pdo->lastInsertId();
            }

            // Update booking record gateway_order_id if not previously set
            $pdo->prepare("UPDATE bookings SET gateway_order_id = COALESCE(gateway_order_id, ?) WHERE id = ?")
                ->execute([$orderId, $bookingId]);

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Logger::error("Payment atomic commit failed for booking #{$bookingId}: " . $e->getMessage());
            return Response::json(['success' => false, 'message' => 'Failed to record verified payment.'], 500);
        }

        // Update booking status and priority (self-healing & backward-compatible)
        $prio = ($paymentType === 'online') ? 'high' : 'normal';
        $this->updateBookingFinancials($bookingId, $totalAmount, $advanceAmount, $dueAmount, $paymentType, $prio, 'new');

        // Only generate invoice and dispatch confirmation notifications if transitioning to paid for the first time
        if (!$isAlreadyPaid) {
            $invoiceRecord = null;
            try {
                $existingInvoice = Database::fetchOne("SELECT * FROM invoices WHERE payment_id = ? LIMIT 1", [$paymentIdDb]);
                if (!$existingInvoice && $paymentIdDb > 0) {
                    $subtotal = round($totalAmount / 1.18, 2);
                    $invoiceRecord = InvoiceBuilder::createForPayment($paymentIdDb, $subtotal, 18.00, $advanceAmount, $dueAmount);
                } else {
                    $invoiceRecord = $existingInvoice;
                }
            } catch (\Throwable $e) {
                Logger::error("Invoice generation skipped: " . $e->getMessage());
            }

            // Send booking confirmation notifications
            try {
                $isNewAcc = !empty($_SESSION['guest_account_created']);
                $bookingRecord = [
                    'id'             => $bookingId,
                    'booking_id'     => $bookingId,
                    'customer_id'    => $booking['customer_id'] ?? null,
                    'booking_no'     => $booking['booking_no'],
                    'service_name'   => $request->input('service_name', 'Doorstep Service'),
                    'name'           => $booking['name'],
                    'phone'          => $booking['phone'],
                    'email'          => $booking['email'] ?? null,
                    'address'        => $booking['address'],
                    'city'           => $booking['pincode'] ? "Pincode: {$booking['pincode']}" : 'Service Area',
                    'preferred_date' => (string)$booking['preferred_date'],
                    'preferred_time' => (string)$booking['preferred_time'],
                    'total_amount'   => $totalAmount,
                    'advance_amount' => $advanceAmount,
                    'due_amount'     => $dueAmount,
                    'payment_type'   => $paymentType,
                    'priority'       => $prio,
                    'is_new_customer'=> $isNewAcc,
                    'login_url'      => View::fullUrl('/login'),
                    'admin_url'      => View::fullUrl('/admin/bookings/' . $bookingId),
                ];

                if ($isNewAcc) {
                    $bookingRecord['set_password_url'] = $_SESSION['guest_set_password_url'] ?? null;
                    $bookingRecord['identifier'] = $_SESSION['guest_account_identifier'] ?? $booking['phone'];
                }

                Notifier::notifyNewBookingAdmin($bookingRecord);
                Notifier::notifyBookingConfirmationCustomer($bookingRecord);

                if ($paymentType === 'online' && $invoiceRecord) {
                    Notifier::notifyPrepaidInvoiceWorkDue($invoiceRecord, $bookingRecord);
                }
            } catch (\Throwable $e) {
                Logger::error("Booking notification error post-payment: " . $e->getMessage());
            }
        }

        // Clear cart
        Cart::clear();

        $paymentSummaryText = ($paymentType === 'online')
            ? 'Prepaid: 100% Full Payment Paid Online (₹' . number_format($totalAmount, 2) . ')'
            : 'Pay After Service: ₹49 Pre-booking Advance Paid · Due to Technician: ₹' . number_format($dueAmount, 2);

        $guestToken = $booking['guest_token'] ?? null;
        $setPasswordUrl = $_SESSION['guest_set_password_url'] ?? null;

        return Response::json([
            'success'            => true,
            'booking_no'         => $booking['booking_no'],
            'booking_id'         => $booking['id'],
            'name'               => $booking['name'],
            'phone'              => $booking['phone'],
            'email'              => $booking['email'] ?? null,
            'identifier'         => $booking['phone'],
            'guest_token'        => $guestToken,
            'set_password_url'   => $setPasswordUrl,
            'service_name'       => $request->input('service_name', 'Doorstep Service'),
            'address'            => $booking['address'],
            'city'               => $booking['city'] ?? 'Service Area',
            'payment_type'       => $paymentType,
            'total_amount'       => $totalAmount,
            'advance_amount'     => $advanceAmount,
            'due_amount'         => $dueAmount,
            'priority'           => $prio,
            'priority_label'     => ($prio === 'high') ? 'High Priority' : 'Medium Priority',
            'payment_method'     => $paymentSummaryText,
            'payment_id'         => $paymentId,
            'redirect_url'       => !empty($guestToken) ? View::url('/book-success?token=' . urlencode($guestToken)) : View::url('/account/bookings'),
        ]);
    }

    public function success(Request $request): Response
    {
        $token = trim((string)$request->query('token', ''));
        $bookingNo = trim((string)$request->query('booking_no', ''));

        $booking = null;

        // 1. Guest access via secure 32+ character random token
        if (!empty($token)) {
            $booking = Booking::findByGuestToken($token);
        } elseif (!empty($bookingNo) && Auth::check()) {
            // 2. Logged-in user: Must match customer_id
            $currentUserId = Auth::id();
            $booking = Database::fetchOne(
                "SELECT b.*, s.name as service_name 
                 FROM bookings b 
                 LEFT JOIN services s ON b.service_id = s.id 
                 WHERE b.booking_no = :bno AND b.customer_id = :cid 
                 LIMIT 1",
                ['bno' => $bookingNo, 'cid' => $currentUserId]
            );
        }

        // Strict 404 if not found or unauthorized
        if (!$booking) {
            return $this->render('partials.404', [
                'title'   => 'Booking Not Found | REFIXEL',
                'message' => 'The requested booking could not be found or you do not have permission to view it.',
            ], null)->setStatusCode(404);
        }

        $res = $this->render('customer.book-success', [
            'title'       => 'Booking Done | REFIXEL',
            'booking_no'  => $booking['booking_no'],
            'booking'     => $booking,
        ], null);
        $res->setHeader('Referrer-Policy', 'no-referrer');
        return $res;
    }

}
