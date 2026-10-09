<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Cart;
use App\Core\Notifier;
use App\Core\Request;
use App\Core\Response;
use App\Core\Upload;
use App\Core\View;
use App\Models\Booking;
use App\Models\BookingAttachment;
use App\Models\Service;
use App\Models\ServiceArea;

class BookingController extends Controller
{
    public function showForm(Request $request): Response
    {
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

    public function submit(Request $request): Response
    {
        $validator = $this->validate($request, [
            'service_id'     => 'required|numeric',
            'name'           => 'required|min:2',
            'phone'          => 'required|phone',
            'address'        => 'required|min:5',
            'preferred_date' => 'required',
            'preferred_time' => 'required',
        ]);

        if ($validator->fails()) {
            View::setFlash('error', $validator->firstError());
            $redirectUrl = $request->input('from_cart') ? '/cart' : '/book?service_id=' . (int)$request->input('service_id');
            return $this->redirect($redirectUrl);
        }

        $serviceId = (int)$request->input('service_id');
        $service = Service::find($serviceId);
        if (!$service || empty($service['is_active'])) {
            View::setFlash('error', 'The requested service is currently unavailable.');
            return $this->redirect('/services');
        }

        // Pincode validation (optional but verified if supplied)
        $pincode = $request->input('pincode') ? trim((string)$request->input('pincode')) : null;
        if ($pincode && !preg_match('/^[0-9]{6}$/', $pincode)) {
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
        $cityInput = trim((string)$request->input('city', 'Gurugram'));
        $stateInput = trim((string)$request->input('state', 'Haryana'));
        $houseNoInput = trim((string)$request->input('house_no'));
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
                $customerId = (int)\App\Core\Database::execute(
                    "INSERT INTO users (name, phone, email, password_hash, role) VALUES (?, ?, ?, ?, 'customer')",
                    [$nameInput, !empty($phoneInput) ? $phoneInput : null, !empty($emailInput) ? $emailInput : null, $hashedPassword],
                    true // Return last insert ID
                );
                $_SESSION['guest_account_created'] = true;
                $_SESSION['guest_account_password'] = $generatedPassword;
                $_SESSION['guest_account_identifier'] = $emailInput ?: $phoneInput;
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
            'preferred_date' => $request->input('preferred_date'),
            'preferred_time' => $request->input('preferred_time'),
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
                        // Log file upload warning without halting booking creation
                        error_log("Issue photo upload skipped: " . $e->getMessage());
                    }
                }
            }
        }

        // Clear cart if booking came from cart checkout
        if ($request->input('from_cart')) {
            Cart::clear();
        }

        // Record DPDP Act consent for booking processing
        \App\Models\Consent::record($customerId, 'booking_terms_and_data_processing', $request->getIp());

        // Trigger notifications (admin alert + customer confirmation)
        try {
            $bookingRecord = [
                'booking_no'     => $bookingNo,
                'service_name'   => $service['name'],
                'name'           => trim((string)$request->input('name')),
                'phone'          => preg_replace('/\D/', '', (string)$request->input('phone')),
                'email'          => $request->input('email') ? trim((string)$request->input('email')) : null,
                'address'        => trim((string)$request->input('address')),
                'city'           => $pincode ? "Pincode: {$pincode}" : 'Service Area',
                'preferred_date' => (string)$request->input('preferred_date'),
                'preferred_time' => (string)$request->input('preferred_time'),
            ];

            if (isset($_SESSION['guest_account_created']) && $_SESSION['guest_account_created'] === true) {
                $bookingRecord['generated_password'] = $_SESSION['guest_account_password'];
                $bookingRecord['identifier'] = $_SESSION['guest_account_identifier'];
            }

            Notifier::notifyNewBookingAdmin($bookingRecord);
            Notifier::notifyBookingConfirmationCustomer($bookingRecord);
        } catch (\Throwable $e) {
            // Notification failure does not break booking completion
            Logger::error("Booking notification error: " . $e->getMessage());
        }

        return $this->redirect('/book-success?booking_no=' . urlencode($bookingNo));
    }

    public function success(Request $request): Response
    {
        $bookingNo = (string)$request->query('booking_no', '');

        return $this->render('customer.book-success', [
            'title'       => 'Booking Confirmed | REFIXEL',
            'description' => 'Your doorstep service booking has been confirmed with REFIXEL.',
            'booking_no'  => $bookingNo,
        ], 'customer');
    }
}

