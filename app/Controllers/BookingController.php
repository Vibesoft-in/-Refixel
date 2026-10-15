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

        return $this->render('customer.cart', [
            'title'       => 'Your Service Cart | REFIXEL',
            'description' => 'Review selected home cleaning and repair packages, calculate prices, and schedule your doorstep visit.',
            'cart'        => $cart,
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

    public function addToCart(Request $request): Response
    {
        // Support both JSON body and standard form post / vendor_price_id
        $serviceId = (int)($request->input('service_id') ?? $request->input('vendor_price_id') ?? 0);
        $qty = (int)($request->input('quantity') ?? 1);

        if ($serviceId <= 0) {
            return Response::json(['success' => false, 'error' => 'Invalid service ID.'], 400);
        }

        $res = Cart::add($serviceId, max(1, $qty));
        $details = Cart::getDetails();

        return Response::json([
            'success'    => $res['success'] ?? true,
            'status'     => $res['success'] ? 'success' : 'error',
            'cart_count' => $details['count'],
            'cart_total' => $details['total'],
            'cart'       => $details,
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
    protected function updateBookingFinancials(int $bookingId, float $totalAmount, float $advanceAmount, float $dueAmount, string $paymentMethod, string $priority, ?string $status = null): void
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

            // Always generate memorable password like Refixel@4829
            $randomDigits = str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
            $generatedPassword = 'Refixel@' . $randomDigits;
            $hashedPassword = password_hash($generatedPassword, PASSWORD_BCRYPT);

            // Checkout authentication & account preparation
            if (!$customerId) {
                $existingUser = null;
                if (!empty($emailInput)) {
                    $existingUser = \App\Models\User::findBy('email', $emailInput);
                }
                if (!$existingUser && !empty($phoneInput)) {
                    $existingUser = \App\Models\User::findBy('phone', $phoneInput);
                }
                
                if ($existingUser) {
                    $customerId = (int)$existingUser['id'];
                    \App\Core\Database::execute("UPDATE users SET password_hash = ? WHERE id = ?", [$hashedPassword, $customerId]);
                    $existingUser['password_hash'] = $hashedPassword;
                } else {
                    try {
                        $customerId = (int)\App\Core\Database::execute(
                            "INSERT INTO users (name, phone, email, password_hash, role) VALUES (?, ?, ?, ?, 'customer')",
                            [$nameInput, !empty($phoneInput) ? $phoneInput : null, !empty($emailInput) ? $emailInput : null, $hashedPassword],
                            true // Return last insert ID
                        );
                    } catch (\Throwable $e) {
                        // Fallback lookup if duplicate was hit
                        $existingUser = null;
                        if (!empty($phoneInput)) {
                            $existingUser = \App\Models\User::findBy('phone', $phoneInput);
                        }
                        if (!$existingUser && !empty($emailInput)) {
                            $existingUser = \App\Models\User::findBy('email', $emailInput);
                        }
                        if ($existingUser) {
                            $customerId = (int)$existingUser['id'];
                            \App\Core\Database::execute("UPDATE users SET password_hash = ? WHERE id = ?", [$hashedPassword, $customerId]);
                            $existingUser['password_hash'] = $hashedPassword;
                        } else {
                            throw $e;
                        }
                    }
                }

                $_SESSION['guest_account_created'] = true;
                $_SESSION['guest_account_password'] = $generatedPassword;
                $_SESSION['guest_account_identifier'] = !empty($phoneInput) ? $phoneInput : ($emailInput ?: '');

                if ($customerId) {
                    $userObj = \App\Models\User::find($customerId);
                    if ($userObj) {
                        \App\Core\Auth::login($userObj);
                    }
                }
            } else {
                // User was already logged in: refresh password in DB so displayed password is active
                \App\Core\Database::execute("UPDATE users SET password_hash = ? WHERE id = ?", [$hashedPassword, $customerId]);
                $_SESSION['guest_account_created'] = true;
                $_SESSION['guest_account_password'] = $generatedPassword;
                $_SESSION['guest_account_identifier'] = !empty($phoneInput) ? $phoneInput : ($emailInput ?: '');
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

            $bookingId = Booking::create([
                'booking_no'     => $bookingNo,
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

            // Record payment record in payments table
            $paymentMethodInput = (string)$request->input('payment_method', 'cod');
            $paymentMethod = ($paymentMethodInput === 'online') ? 'online' : 'cod';
            
            $totalServiceAmount = (float)($service['starting_price'] ?? 0);
            if ($request->input('from_cart')) {
                $cartData = Cart::getDetails();
                if (!empty($cartData['total'])) {
                    $totalServiceAmount = (float)$cartData['total'];
                }
                // Clear cart only AFTER obtaining details
                Cart::clear();
            }
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

                \App\Models\Payment::create([
                    'booking_id'      => $bookingId,
                    'amount'          => $advanceAmount,
                    'method'          => 'upi',
                    'status'          => 'pending',
                    'transaction_ref' => $rzpOrder['order_id'] ?? null,
                    'paid_at'         => null,
                    'recorded_by'     => $validCustomerId ?: null,
                ]);
            } catch (\Throwable $e) {
                Logger::error("Razorpay order creation error for booking #{$bookingId}: " . $e->getMessage());
            }

            if ($request->isAjax() || $request->input('is_ajax')) {
                $isNewAcc = !empty($_SESSION['guest_account_created']);
                $genPwd = $_SESSION['guest_account_password'] ?? null;
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
                        'generated_password' => $genPwd,
                        'identifier'         => $ident,
                        'redirect_url'       => View::url('/account/bookings'),
                    ]);
                }

                // If direct booking without online payment requirement, dispatch notifications:
                try {
                    $directRecord = [
                        'id'             => $bookingId,
                        'booking_id'     => $bookingId,
                        'booking_no'     => $bookingNo,
                        'service_name'   => $service['name'] ?? 'Doorstep Service',
                        'name'           => $nameInput,
                        'phone'          => $phoneInput,
                        'email'          => $emailInput ?? null,
                        'address'        => $addressInput,
                        'city'           => $cityInput,
                        'preferred_date' => $prefDate,
                        'preferred_time' => $prefTime,
                        'total_amount'   => $totalServiceAmount,
                        'advance_amount' => 0.00,
                        'due_amount'     => $totalServiceAmount,
                        'payment_type'   => 'cod',
                        'priority'       => $priority,
                        'is_new_customer'=> $isNewAcc,
                        'login_url'      => View::fullUrl('/login'),
                        'admin_url'      => View::fullUrl('/admin/bookings/' . $bookingId),
                    ];
                    if ($isNewAcc && $genPwd) {
                        $directRecord['generated_password'] = $genPwd;
                        $directRecord['identifier'] = $ident;
                    }
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
                    'generated_password' => $genPwd,
                    'identifier'         => $ident,
                    'redirect_url'       => View::url('/account/bookings'),
                ]);
            }

            // Direct non-AJAX booking fallback notification:
            try {
                $directRecord = [
                    'id'             => $bookingId,
                    'booking_id'     => $bookingId,
                    'booking_no'     => $bookingNo,
                    'service_name'   => $service['name'] ?? 'Doorstep Service',
                    'name'           => $nameInput,
                    'phone'          => $phoneInput,
                    'email'          => $emailInput ?? null,
                    'address'        => $addressInput,
                    'city'           => $cityInput,
                    'preferred_date' => $prefDate,
                    'preferred_time' => $prefTime,
                    'total_amount'   => $totalServiceAmount,
                    'advance_amount' => 0.00,
                    'due_amount'     => $totalServiceAmount,
                    'payment_type'   => 'cod',
                    'priority'       => $priority,
                    'is_new_customer'=> !empty($_SESSION['guest_account_created']),
                    'login_url'      => View::fullUrl('/login'),
                    'admin_url'      => View::fullUrl('/admin/bookings/' . $bookingId),
                ];
                if (!empty($_SESSION['guest_account_created'])) {
                    $directRecord['generated_password'] = $_SESSION['guest_account_password'] ?? null;
                    $directRecord['identifier'] = $_SESSION['guest_account_identifier'] ?? $phoneInput;
                }
                Notifier::notifyNewBookingAdmin($directRecord);
                Notifier::notifyBookingConfirmationCustomer($directRecord);
            } catch (\Throwable $ne) {}

            return $this->redirect('/book-success?booking_no=' . urlencode($bookingNo));
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
        $orderId = (string)$request->input('razorpay_order_id');
        $paymentId = (string)$request->input('razorpay_payment_id');
        $signature = (string)$request->input('razorpay_signature');

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

        $totalAmount = (float)($booking['total_amount'] ?? 0);
        if ($totalAmount <= 0) {
            $totalAmount = 899.00;
        }
        $paymentType = (string)($booking['payment_type'] ?? 'cod');
        $advanceAmount = ($paymentType === 'online') ? $totalAmount : 49.00;
        $dueAmount = max(0.00, round($totalAmount - $advanceAmount, 2));

        // Update payment record in database as paid
        $payment = Database::fetchOne("SELECT * FROM payments WHERE booking_id = ? ORDER BY id DESC LIMIT 1", [$bookingId]);
        if ($payment) {
            Database::execute(
                "UPDATE payments SET status = 'paid', method = 'upi', amount = ?, transaction_ref = ?, paid_at = NOW() WHERE id = ?",
                [$advanceAmount, $paymentId, $payment['id']]
            );
            $paymentIdDb = (int)$payment['id'];
        } else {
            $paymentIdDb = (int)Database::execute(
                "INSERT INTO payments (booking_id, amount, method, status, transaction_ref, paid_at) VALUES (?, ?, 'upi', 'paid', ?, NOW())",
                [$bookingId, $advanceAmount, $paymentId],
                true
            );
        }

        // Generate GST Invoice with total, advance_paid and due_amount
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

        // Update booking status and priority (self-healing & backward-compatible)
        $prio = ($paymentType === 'online') ? 'high' : 'normal';
        $this->updateBookingFinancials($bookingId, $totalAmount, $advanceAmount, $dueAmount, $paymentType, $prio, 'new');

        // Send booking confirmation notifications
        try {
            $isNewAcc = !empty($_SESSION['guest_account_created']);
            $bookingRecord = [
                'id'             => $bookingId,
                'booking_id'     => $bookingId,
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
                $bookingRecord['generated_password'] = $_SESSION['guest_account_password'] ?? null;
                $bookingRecord['identifier'] = $_SESSION['guest_account_identifier'] ?? $booking['phone'];
            }

            Notifier::notifyNewBookingAdmin($bookingRecord);
            Notifier::notifyBookingConfirmationCustomer($bookingRecord);

            // If prepaid online full payment was made, also send the Advance Payment Receipt noting "Work is Due"
            if ($paymentType === 'online' && $invoiceRecord) {
                Notifier::notifyPrepaidInvoiceWorkDue($invoiceRecord, $bookingRecord);
            }
        } catch (\Throwable $e) {
            Logger::error("Booking notification error post-payment: " . $e->getMessage());
        }

        // Clear cart
        Cart::clear();

        $paymentSummaryText = ($paymentType === 'online')
            ? 'Prepaid: 100% Full Payment Paid Online (₹' . number_format($totalAmount, 2) . ')'
            : 'Pay After Service: ₹49 Pre-booking Advance Paid · Due to Technician: ₹' . number_format($dueAmount, 2);

        $displayDigits = substr(preg_replace('/\D/', '', (string)($booking['phone'] ?? '1234')), -4);
        $returnPassword = $_SESSION['guest_account_password'] ?? ('Refixel@' . ($displayDigits ?: '1234'));

        return Response::json([
            'success'            => true,
            'booking_no'         => $booking['booking_no'],
            'booking_id'         => $booking['id'],
            'name'               => $booking['name'],
            'phone'              => $booking['phone'],
            'email'              => $booking['email'] ?? null,
            'identifier'         => $booking['phone'],
            'generated_password' => $returnPassword,
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
            'redirect_url'       => View::url('/account/bookings'),
        ]);
    }

    public function success(Request $request): Response
    {
        $bookingNo = (string)$request->query('booking_no', '');

        $booking = null;
        if (!empty($bookingNo)) {
            $booking = Database::fetchOne(
                "SELECT b.*, s.name as service_name 
                 FROM bookings b 
                 LEFT JOIN services s ON b.service_id = s.id 
                 WHERE b.booking_no = ? 
                 LIMIT 1",
                [$bookingNo]
            );
        }

        return $this->render('customer.book-success', [
            'title'       => 'Booking Done | REFIXEL',
            'booking_no'  => $bookingNo,
            'booking'     => $booking,
        ], null);
    }

    public function executeEnquiryCreation(array $data): array
    {
        $name         = trim((string)($data['name'] ?? ''));
        $rawPhone     = trim((string)($data['phone'] ?? ''));
        $rawEmail     = trim((string)($data['email'] ?? ''));
        $serviceInput = trim((string)($data['service'] ?? ''));
        $location     = trim((string)($data['location'] ?? $data['detected_location'] ?? ''));
        $message      = trim((string)($data['message'] ?? ''));

        // Clean phone number (strip spaces, symbols, +91)
        $cleanPhone = preg_replace('/\D/', '', $rawPhone);
        if (str_starts_with($cleanPhone, '91') && strlen($cleanPhone) === 12) {
            $cleanPhone = substr($cleanPhone, 2);
        }

        // Resolve service ID from name, slug or keyword
        $serviceId = $this->resolveServiceId($serviceInput);
        $service = Service::find($serviceId);

        // Generate unique Booking/Enquiry reference number (e.g. BK-XXXXXX)
        $bookingNo = Booking::generateBookingNo();

        // Customer handling: Auto account generation if new user
        $customerId = Auth::id();
        if ($customerId && !User::find($customerId)) {
            $customerId = null;
        }
        $isNewCustomer = false;
        // Always generate a memorable password like Refixel@4829 for the enquiry
        $randomDigits = str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        $generatedPassword = 'Refixel@' . $randomDigits;
        $hashedPassword = password_hash($generatedPassword, PASSWORD_BCRYPT);

        if (!$customerId) {
            // Check if user already exists with this phone or email
            $existingUser = User::findBy('phone', $cleanPhone);
            if (!$existingUser && !empty($rawEmail)) {
                $existingUser = User::findBy('email', $rawEmail);
            }

            if ($existingUser) {
                $customerId = (int)$existingUser['id'];
                // Update password so customer always has a fresh active password, plus name/email
                Database::execute(
                    "UPDATE users SET password_hash = ?, name = IF(name IS NULL OR name = '', ?, name), email = IF(email IS NULL OR email = '', ?, email) WHERE id = ?",
                    [$hashedPassword, $name, !empty($rawEmail) ? $rawEmail : null, $customerId]
                );
                $existingUser['password_hash'] = $hashedPassword;
                Auth::login($existingUser);
            } else {
                $customerId = (int)Database::execute(
                    "INSERT INTO users (name, phone, email, password_hash, role, status) VALUES (?, ?, ?, ?, 'customer', 'active')",
                    [$name, $cleanPhone, !empty($rawEmail) ? $rawEmail : null, $hashedPassword],
                    true
                );

                if ($customerId) {
                    $isNewCustomer = true;
                    // Seed customer profile safely
                    try {
                        Database::execute(
                            "INSERT INTO customer_profiles (user_id, address, city, address_type) VALUES (?, ?, ?, 'Home')",
                            [$customerId, $location ?: 'Uttarakhand Service Area', 'Kashipur']
                        );
                    } catch (\Throwable $e) {}

                    // Seed default customer address safely
                    if (!empty($location)) {
                        try {
                            Database::execute(
                                "INSERT INTO customer_addresses (user_id, label, address, city, is_default) VALUES (?, 'Home', ?, 'Kashipur', 1)",
                                [$customerId, $location]
                            );
                        } catch (\Throwable $e) {}
                    }

                    $newUser = User::find($customerId);
                    if ($newUser) {
                        Auth::login($newUser);
                    }
                }
            }
        } else {
            // Already logged in user submitting another enquiry: refresh password in DB so displayed password is active
            Database::execute("UPDATE users SET password_hash = ? WHERE id = ?", [$hashedPassword, $customerId]);
        }

        // Insert booking / enquiry record with status 'new'
        $bookingId = Booking::create([
            'booking_no'     => $bookingNo,
            'customer_id'    => $customerId ?: null,
            'service_id'     => $serviceId,
            'name'           => $name,
            'phone'          => $cleanPhone,
            'email'          => !empty($rawEmail) ? $rawEmail : null,
            'address'        => $location ?: 'Uttarakhand Service Area',
            'pincode'        => null,
            'preferred_date' => date('Y-m-d'),
            'preferred_time' => 'Immediate / ASAP',
            'issue_details'  => $message ?: 'Quick enquiry submitted from homepage (Verified Email)',
            'status'         => 'new',
            'priority'       => 'high',
        ]);

        // Save session data for confirmation page
        $_SESSION['last_enquiry'] = [
            'booking_no'         => $bookingNo,
            'booking_id'         => $bookingId,
            'name'               => $name,
            'phone'              => $cleanPhone,
            'email'              => $rawEmail,
            'service_name'       => $service ? $service['name'] : ($serviceInput ?: 'Home Maintenance Service'),
            'location'           => $location ?: 'Uttarakhand Service Area',
            'message'            => $message,
            'is_new_customer'    => $isNewCustomer,
            'identifier'         => $cleanPhone,
            'generated_password' => $generatedPassword,
        ];

        // Trigger Notifications
        try {
            $record = [
                'id'                 => $bookingId,
                'booking_id'         => $bookingId,
                'booking_no'         => $bookingNo,
                'service_name'       => $service ? $service['name'] : ($serviceInput ?: 'Home Service'),
                'name'               => $name,
                'phone'              => $cleanPhone,
                'email'              => !empty($rawEmail) ? $rawEmail : null,
                'address'            => $location ?: 'Service Area',
                'preferred_date'     => date('Y-m-d'),
                'preferred_time'     => 'Immediate / ASAP',
                'message'            => $message,
                'is_new_customer'    => $isNewCustomer,
                'login_url'          => View::fullUrl('/login'),
                'admin_url'          => View::fullUrl('/admin/bookings/' . $bookingId),
                'generated_password' => $generatedPassword,
                'identifier'         => $cleanPhone,
            ];
            Notifier::notifyNewBookingAdmin($record);
            Notifier::notifyBookingConfirmationCustomer($record);
        } catch (\Throwable $e) {
            Logger::error("Enquiry notification note: " . $e->getMessage());
        }

        return [
            'booking_id' => $bookingId,
            'booking_no' => $bookingNo,
        ];
    }

    public function quickEnquiry(Request $request): Response
    {
        $name     = trim((string)$request->input('name', ''));
        $rawPhone = trim((string)$request->input('phone', ''));
        $rawEmail = trim((string)$request->input('email', ''));
        $otp      = trim((string)$request->input('otp', ''));

        // If AJAX request, delegate to OTP sending
        if ($request->isAjax() || $request->isJson()) {
            return $this->apiSendEnquiryOtp($request);
        }

        // Non-AJAX fallback if OTP was submitted
        if (!empty($otp) && !empty($_SESSION['enquiry_otp']['otp_hash']) && password_verify($otp, $_SESSION['enquiry_otp']['otp_hash'])) {
            $formData = $_SESSION['enquiry_otp']['data'] ?? $request->all();
            unset($_SESSION['enquiry_otp']);
            $res = $this->executeEnquiryCreation($formData);
            return $this->redirect('/enquiry-success?booking_no=' . urlencode($res['booking_no']));
        }

        if (empty($name) || empty($rawPhone)) {
            View::setFlash('error', 'Please enter your name and a valid phone number.');
            return $this->redirect('/#homeContactSection');
        }

        // If email provided without OTP, initiate OTP and notify
        if (!empty($rawEmail)) {
            $this->apiSendEnquiryOtp($request);
            View::setFlash('info', 'Verification code sent! Please verify your email.');
            return $this->redirect('/#homeContactSection');
        }

        // Fallback direct creation
        $res = $this->executeEnquiryCreation($request->all());
        return $this->redirect('/enquiry-success?booking_no=' . urlencode($res['booking_no']));
    }

    public function apiSendEnquiryOtp(Request $request): Response
    {
        $name         = trim((string)$request->input('name', ''));
        $rawPhone     = trim((string)$request->input('phone', ''));
        $rawEmail     = trim((string)$request->input('email', ''));
        $serviceInput = trim((string)$request->input('service', ''));
        $location     = trim((string)($request->input('location') ?? $request->input('detected_location') ?? ''));
        $message      = trim((string)$request->input('message', ''));

        $cleanPhone = preg_replace('/\D/', '', $rawPhone);
        if (str_starts_with($cleanPhone, '91') && strlen($cleanPhone) === 12) {
            $cleanPhone = substr($cleanPhone, 2);
        }

        if (empty($name)) {
            return Response::json(['status' => 'error', 'message' => 'Please enter your name.'], 422);
        }
        if (empty($cleanPhone) || strlen($cleanPhone) < 10) {
            return Response::json(['status' => 'error', 'message' => 'Please enter a valid 10-digit mobile number.'], 422);
        }
        if (empty($rawEmail) || !filter_var($rawEmail, FILTER_VALIDATE_EMAIL)) {
            return Response::json(['status' => 'error', 'message' => 'Please enter a valid email address to receive the verification OTP.'], 422);
        }

        // Generate 6-digit numeric OTP
        $otp = (string)random_int(100000, 999999);

        // Store pending enquiry in session
        $_SESSION['enquiry_otp'] = [
            'otp_hash'   => password_hash($otp, PASSWORD_BCRYPT),
            'email'      => $rawEmail,
            'expires_at' => time() + 600, // 10 minutes
            'resend_at'  => time() + 30,  // 30 seconds cooldown
            'attempts'   => 0,
            'data'       => [
                'name'              => $name,
                'phone'             => $cleanPhone,
                'email'             => $rawEmail,
                'service'           => $serviceInput,
                'location'          => $location,
                'message'           => $message,
                'detected_location' => $location,
            ],
        ];

        // Send OTP email
        $sent = false;
        try {
            $sent = Notifier::notifyEnquiryOtp($rawEmail, $otp, [
                'name'    => $name,
                'service' => $serviceInput,
                'phone'   => $cleanPhone,
            ]);
        } catch (\Throwable $e) {
            Logger::error("Enquiry OTP dispatch error: " . $e->getMessage());
        }

        // Mask email for display: r***@domain.com
        $parts = explode('@', $rawEmail);
        $namePart = $parts[0];
        $domainPart = $parts[1] ?? '';
        $maskedName = strlen($namePart) > 2 ? substr($namePart, 0, 1) . str_repeat('*', max(1, strlen($namePart) - 2)) . substr($namePart, -1) : substr($namePart, 0, 1) . '***';
        $maskedEmail = $maskedName . '@' . $domainPart;

        return Response::json([
            'status'       => 'success',
            'message'      => 'A 6-digit verification code has been sent to ' . $rawEmail,
            'masked_email' => $maskedEmail,
            'email'        => $rawEmail,
            'cooldown'     => 30,
        ]);
    }

    public function apiResendEnquiryOtp(Request $request): Response
    {
        $sessionOtp = $_SESSION['enquiry_otp'] ?? null;
        if (!$sessionOtp || empty($sessionOtp['email'])) {
            return Response::json([
                'status'  => 'error',
                'message' => 'Verification session expired. Please submit the enquiry form again.'
            ], 400);
        }

        $now = time();
        if ($now < ($sessionOtp['resend_at'] ?? 0)) {
            $remain = (int)($sessionOtp['resend_at'] - $now);
            return Response::json([
                'status'  => 'error',
                'message' => "Please wait {$remain} seconds before requesting a new OTP."
            ], 429);
        }

        $otp = (string)random_int(100000, 999999);
        $_SESSION['enquiry_otp']['otp_hash']   = password_hash($otp, PASSWORD_BCRYPT);
        $_SESSION['enquiry_otp']['expires_at'] = $now + 600;
        $_SESSION['enquiry_otp']['resend_at']  = $now + 30;
        $_SESSION['enquiry_otp']['attempts']   = 0;

        $email = $sessionOtp['email'];
        $data  = $sessionOtp['data'] ?? [];

        try {
            Notifier::notifyEnquiryOtp($email, $otp, $data);
        } catch (\Throwable $e) {
            Logger::error("Resend Enquiry OTP dispatch error: " . $e->getMessage());
        }

        return Response::json([
            'status'   => 'success',
            'message'  => 'A fresh verification code has been sent to ' . $email . '.',
            'cooldown' => 30,
        ]);
    }

    public function apiVerifyEnquiryOtp(Request $request): Response
    {
        $sessionOtp = $_SESSION['enquiry_otp'] ?? null;
        if (!$sessionOtp || empty($sessionOtp['email'])) {
            return Response::json([
                'status'  => 'error',
                'message' => 'Verification session expired. Please fill out the form again.'
            ], 400);
        }

        $now = time();
        if ($now > ($sessionOtp['expires_at'] ?? 0)) {
            return Response::json([
                'status'  => 'error',
                'message' => 'OTP has expired. Please click Resend OTP to receive a fresh code.'
            ], 400);
        }

        if (($sessionOtp['attempts'] ?? 0) >= 5) {
            unset($_SESSION['enquiry_otp']);
            return Response::json([
                'status'  => 'error',
                'message' => 'Too many invalid attempts. Please submit the enquiry form again.'
            ], 400);
        }

        $inputOtp = trim((string)$request->input('otp', ''));
        $cleanOtp = preg_replace('/\D/', '', $inputOtp);

        if (empty($cleanOtp) || strlen($cleanOtp) !== 6) {
            return Response::json([
                'status'  => 'error',
                'message' => 'Please enter the complete 6-digit OTP code.'
            ], 422);
        }

        if (!password_verify($cleanOtp, $sessionOtp['otp_hash'])) {
            $_SESSION['enquiry_otp']['attempts'] = ($sessionOtp['attempts'] ?? 0) + 1;
            $remaining = 5 - $_SESSION['enquiry_otp']['attempts'];
            return Response::json([
                'status'  => 'error',
                'message' => "Invalid OTP code. Please check your email and enter the correct code. ({$remaining} attempt(s) remaining)",
                'remaining_attempts' => $remaining,
            ], 400);
        }

        // Verified! Execute enquiry creation
        $formData = $sessionOtp['data'] ?? [];
        unset($_SESSION['enquiry_otp']);

        $res = $this->executeEnquiryCreation($formData);

        return Response::json([
            'status'       => 'success',
            'message'      => 'Enquiry successfully verified and submitted!',
            'booking_no'   => $res['booking_no'],
            'redirect_url' => View::url('/enquiry-success?booking_no=' . urlencode($res['booking_no'])),
        ]);
    }

    public function enquirySuccess(Request $request): Response
    {
        $bookingNo = (string)$request->query('booking_no', '');
        $enquiryData = $_SESSION['last_enquiry'] ?? null;

        // Fallback: If session data was lost or page was reloaded, fetch from DB
        if ((!$enquiryData || ($enquiryData['booking_no'] ?? '') !== $bookingNo) && !empty($bookingNo)) {
            $row = Database::fetchOne(
                "SELECT b.*, s.name as service_name 
                 FROM bookings b 
                 LEFT JOIN services s ON b.service_id = s.id 
                 WHERE b.booking_no = ? 
                 LIMIT 1",
                [$bookingNo]
            );
            if ($row) {
                    $fallbackDigits = substr(preg_replace('/\D/', '', (string)($row['phone'] ?? '1234')), -4);
                    $enquiryData = [
                        'booking_no'         => $row['booking_no'],
                        'booking_id'         => $row['id'],
                        'name'               => $row['name'],
                        'phone'              => $row['phone'],
                        'email'              => $row['email'] ?? null,
                        'service_name'       => $row['service_name'] ?? 'Home Service',
                        'location'           => $row['address'],
                        'message'            => $row['issue_details'],
                        'is_new_customer'    => false,
                        'identifier'         => $row['phone'],
                        'generated_password' => 'Refixel@' . ($fallbackDigits ?: '1234'),
                    ];
            }
        }

        return $this->render('customer.enquiry-success', [
            'title'       => 'Booking Done | REFIXEL',
            'description' => 'Your booking enquiry has been registered with REFIXEL.',
            'enquiry'     => $enquiryData,
            'booking_no'  => $bookingNo,
        ], null);
    }

    protected function resolveServiceId(string $serviceInput): int
    {
        if (is_numeric($serviceInput) && (int)$serviceInput > 0) {
            $row = Database::fetchOne("SELECT id FROM services WHERE id = ?", [(int)$serviceInput]);
            if ($row) return (int)$row['id'];
        }

        if (!empty($serviceInput)) {
            $row = Database::fetchOne("SELECT id FROM services WHERE name = ? OR slug = ?", [$serviceInput, $serviceInput]);
            if ($row) return (int)$row['id'];

            $keywords = ['cleaning', 'plumb', 'pipe', 'leak', 'ac', 'paint', 'pest', 'carpent', 'electric', 'appliance', 'ceiling', 'wash'];
            foreach ($keywords as $kw) {
                if (stripos($serviceInput, $kw) !== false) {
                    $row = Database::fetchOne("SELECT id FROM services WHERE name LIKE ? OR slug LIKE ?", ["%{$kw}%", "%{$kw}%"]);
                    if ($row) return (int)$row['id'];
                }
            }
        }

        $first = Database::fetchOne("SELECT id FROM services WHERE is_active = 1 ORDER BY id ASC LIMIT 1");
        return (int)($first['id'] ?? 1);
    }
}

