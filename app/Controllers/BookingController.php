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

            // Guest Checkout: check if user exists, else create new account
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
                } else {
                    $generatedPassword = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$"), 0, 8);
                    $hashedPassword = password_hash($generatedPassword, PASSWORD_BCRYPT);
                    try {
                        $customerId = (int)\App\Core\Database::execute(
                            "INSERT INTO users (name, phone, email, password_hash, role) VALUES (?, ?, ?, ?, 'customer')",
                            [$nameInput, !empty($phoneInput) ? $phoneInput : null, !empty($emailInput) ? $emailInput : null, $hashedPassword],
                            true // Return last insert ID
                        );
                        $_SESSION['guest_account_created'] = true;
                        $_SESSION['guest_account_password'] = $generatedPassword;
                        $_SESSION['guest_account_identifier'] = $emailInput ?: $phoneInput;
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
                        } else {
                            throw $e;
                        }
                    }
                }

                if ($customerId) {
                    $userObj = \App\Models\User::find($customerId);
                    if ($userObj) {
                        \App\Core\Auth::login($userObj);
                    }
                }
            }

            if ($customerId) {
                $existingProfile = \App\Core\Database::fetchOne("SELECT id FROM customer_profiles WHERE user_id = ?", [$customerId]);
                if ($existingProfile) {
                    \App\Core\Database::execute("UPDATE customer_profiles SET address = ?, pincode = ?, city = ?, state = ?, house_no = ?, street = ?, address_type = ? WHERE user_id = ?", [$addressInput, $pincode, $cityInput, $stateInput, $houseNoInput, $streetInput, $addressTypeInput, $customerId]);
                } else {
                    \App\Core\Database::execute("INSERT INTO customer_profiles (user_id, address, pincode, city, state, house_no, street, address_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [$customerId, $addressInput, $pincode, $cityInput, $stateInput, $houseNoInput, $streetInput, $addressTypeInput]);
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

            $bookingId = Booking::create([
                'booking_no'     => $bookingNo,
                'customer_id'    => $customerId,
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
            \App\Models\Consent::record($customerId, 'booking_terms_and_data_processing', $request->getIp());

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
            $priority = ($paymentMethod === 'online') ? 'high' : 'normal';

            // Update booking financial tracking columns
            Database::execute(
                "UPDATE bookings SET total_amount = ?, advance_amount = ?, due_amount = ?, payment_type = ?, priority = ? WHERE id = ?",
                [$totalServiceAmount, $advanceAmount, $dueAmount, $paymentMethod, $priority, $bookingId]
            );

            $rzpOrder = null;
            try {
                $rzpGateway = new RazorpayGateway();
                $rzpOrder = $rzpGateway->createOrder($bookingId, $advanceAmount, 'INR', [
                    'booking_no'     => $bookingNo,
                    'service_name'   => $service['name'] ?? 'Doorstep Service',
                    'customer_id'    => (string)$customerId,
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
                    'recorded_by'     => $customerId ?: null,
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
                    'payment_method'     => 'Cash on Delivery',
                    'is_new_customer'    => $isNewAcc,
                    'generated_password' => $genPwd,
                    'identifier'         => $ident,
                    'redirect_url'       => View::url('/account/bookings'),
                ]);
            }

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
        try {
            $existingInvoice = Database::fetchOne("SELECT id FROM invoices WHERE payment_id = ? LIMIT 1", [$paymentIdDb]);
            if (!$existingInvoice && $paymentIdDb > 0) {
                $subtotal = round($totalAmount / 1.18, 2);
                InvoiceBuilder::createForPayment($paymentIdDb, $subtotal, 18.00, $advanceAmount, $dueAmount);
            }
        } catch (\Throwable $e) {
            Logger::error("Invoice generation skipped: " . $e->getMessage());
        }

        // Update booking status and priority
        $prio = ($paymentType === 'online') ? 'high' : 'normal';
        Database::execute(
            "UPDATE bookings SET priority = ?, status = 'new', total_amount = ?, advance_amount = ?, due_amount = ? WHERE id = ?",
            [$prio, $totalAmount, $advanceAmount, $dueAmount, $bookingId]
        );

        // Send booking confirmation notifications
        try {
            $bookingRecord = [
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
            ];

            if (isset($_SESSION['guest_account_created']) && $_SESSION['guest_account_created'] === true) {
                $bookingRecord['generated_password'] = $_SESSION['guest_account_password'];
                $bookingRecord['identifier'] = $_SESSION['guest_account_identifier'];
            }

            Notifier::notifyNewBookingAdmin($bookingRecord);
            Notifier::notifyBookingConfirmationCustomer($bookingRecord);
        } catch (\Throwable $e) {
            Logger::error("Booking notification error post-payment: " . $e->getMessage());
        }

        // Clear cart
        Cart::clear();

        $paymentSummaryText = ($paymentType === 'online')
            ? '100% Full Payment Paid Online (₹' . number_format($totalAmount, 2) . ')'
            : 'Pre-booking Advance Paid: ₹' . number_format($advanceAmount, 2) . ' · Due to Technician: ₹' . number_format($dueAmount, 2);

        return Response::json([
            'success'            => true,
            'booking_no'         => $booking['booking_no'],
            'booking_id'         => $booking['id'],
            'name'               => $booking['name'],
            'phone'              => $booking['phone'],
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

    public function quickEnquiry(Request $request): Response
    {
        $name         = trim((string)$request->input('name', ''));
        $rawPhone     = trim((string)$request->input('phone', ''));
        $serviceInput = trim((string)$request->input('service', ''));
        $location     = trim((string)($request->input('location') ?? $request->input('detected_location') ?? ''));
        $message      = trim((string)$request->input('message', ''));

        // Clean phone number (strip spaces, symbols, +91)
        $cleanPhone = preg_replace('/\D/', '', $rawPhone);
        if (str_starts_with($cleanPhone, '91') && strlen($cleanPhone) === 12) {
            $cleanPhone = substr($cleanPhone, 2);
        }

        if (empty($name) || empty($cleanPhone)) {
            View::setFlash('error', 'Please enter your name and a valid phone number.');
            return $this->redirect('/#homeContactSection');
        }

        // Resolve service ID from name, slug or keyword
        $serviceId = $this->resolveServiceId($serviceInput);
        $service = Service::find($serviceId);

        // Generate unique Booking/Enquiry reference number (e.g. BK-XXXXXX)
        $bookingNo = Booking::generateBookingNo();

        // Customer handling: Auto account generation if new user
        $customerId = Auth::id();
        $isNewCustomer = false;
        $generatedPassword = null;

        if (!$customerId) {
            // Check if user already exists with this phone
            $existingUser = User::findBy('phone', $cleanPhone);
            if ($existingUser) {
                $customerId = (int)$existingUser['id'];
                // Update name if previously empty or placeholder
                if (!empty($name) && empty($existingUser['name'])) {
                    Database::execute("UPDATE users SET name = ? WHERE id = ?", [$name, $customerId]);
                    $existingUser['name'] = $name;
                }
                Auth::login($existingUser);
            } else {
                // Generate authentic, memorable password like Refixel@4829
                $randomDigits = str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
                $generatedPassword = 'Refixel@' . $randomDigits;
                $hashedPassword = password_hash($generatedPassword, PASSWORD_BCRYPT);

                $customerId = (int)Database::execute(
                    "INSERT INTO users (name, phone, email, password_hash, role, status) VALUES (?, ?, ?, ?, 'customer', 'active')",
                    [$name, $cleanPhone, null, $hashedPassword],
                    true
                );

                if ($customerId) {
                    $isNewCustomer = true;
                    // Seed customer profile
                    Database::execute(
                        "INSERT INTO customer_profiles (user_id, address, city, address_type) VALUES (?, ?, ?, 'Home')",
                        [$customerId, $location ?: 'Uttarakhand Service Area', 'Kashipur']
                    );

                    // Seed default customer address
                    if (!empty($location)) {
                        Database::execute(
                            "INSERT INTO customer_addresses (user_id, label, address, city, is_default) VALUES (?, 'Home', ?, 'Kashipur', 1)",
                            [$customerId, $location]
                        );
                    }

                    $newUser = User::find($customerId);
                    if ($newUser) {
                        Auth::login($newUser);
                    }
                }
            }
        }

        // Insert booking / enquiry record with status 'new'
        $bookingId = Booking::create([
            'booking_no'     => $bookingNo,
            'customer_id'    => $customerId ?: null,
            'service_id'     => $serviceId,
            'name'           => $name,
            'phone'          => $cleanPhone,
            'email'          => null,
            'address'        => $location ?: 'Uttarakhand Service Area',
            'pincode'        => null,
            'preferred_date' => date('Y-m-d'),
            'preferred_time' => 'Immediate / ASAP',
            'issue_details'  => $message ?: 'Quick enquiry submitted from homepage',
            'status'         => 'new',
            'priority'       => 'high',
        ]);

        // Save session data for confirmation page
        $_SESSION['last_enquiry'] = [
            'booking_no'         => $bookingNo,
            'booking_id'         => $bookingId,
            'name'               => $name,
            'phone'              => $cleanPhone,
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
                'booking_no'     => $bookingNo,
                'service_name'   => $service ? $service['name'] : ($serviceInput ?: 'Home Service'),
                'name'           => $name,
                'phone'          => $cleanPhone,
                'address'        => $location ?: 'Service Area',
                'preferred_date' => date('Y-m-d'),
                'preferred_time' => 'Immediate / ASAP',
            ];
            if ($isNewCustomer && $generatedPassword) {
                $record['generated_password'] = $generatedPassword;
                $record['identifier'] = $cleanPhone;
            }
            Notifier::notifyNewBookingAdmin($record);
            Notifier::notifyBookingConfirmationCustomer($record);
        } catch (\Throwable $e) {
            Logger::error("Enquiry notification note: " . $e->getMessage());
        }

        return $this->redirect('/enquiry-success?booking_no=' . urlencode($bookingNo));
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
                $enquiryData = [
                    'booking_no'         => $row['booking_no'],
                    'booking_id'         => $row['id'],
                    'name'               => $row['name'],
                    'phone'              => $row['phone'],
                    'service_name'       => $row['service_name'] ?? 'Home Service',
                    'location'           => $row['address'],
                    'message'            => $row['issue_details'],
                    'is_new_customer'    => false,
                    'identifier'         => $row['phone'],
                    'generated_password' => null,
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

