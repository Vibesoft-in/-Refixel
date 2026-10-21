<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Middleware\VerifyCsrf;

echo "========================================================\n";
echo "   TEST SUITE: CSRF PROTECTION & HEADER VERIFICATION    \n";
echo "========================================================\n\n";

$middleware = new VerifyCsrf();
$validToken = Csrf::token();

// --------------------------------------------------------------------------
// TEST 1: Web POST request without CSRF token returns HTTP 419
// --------------------------------------------------------------------------
echo "[TEST 1] Testing web form POST without token -> Must return HTTP 419...\n";

$reqNoToken = new Request([], ['name' => 'John Doe'], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/contact',
]);

$resp = $middleware->handle($reqNoToken);
assert($resp instanceof Response, "FAIL: Expected Response object for rejected request");
assert($resp->getStatusCode() === 419, "FAIL: Expected HTTP 419, got " . $resp->getStatusCode());
assert(str_contains($resp->getBody(), '419 Page Expired'), "FAIL: Expected 419 Page Expired in body");

echo "  ✓ PASS: Web form POST without token rejected with HTTP 419.\n\n";

// --------------------------------------------------------------------------
// TEST 2: AJAX / JSON POST request without CSRF token returns HTTP 419 JSON
// --------------------------------------------------------------------------
echo "[TEST 2] Testing AJAX POST without token -> Must return HTTP 419 JSON...\n";

$reqAjaxNoToken = new Request([], ['city' => 'Kashipur'], [
    'REQUEST_METHOD'         => 'POST',
    'REQUEST_URI'            => '/api/service-areas',
    'HTTP_X_REQUESTED_WITH'  => 'XMLHttpRequest',
]);

$respAjax = $middleware->handle($reqAjaxNoToken);
assert($respAjax instanceof Response, "FAIL: Expected Response object for rejected AJAX request");
assert($respAjax->getStatusCode() === 419, "FAIL: Expected HTTP 419, got " . $respAjax->getStatusCode());
$json = json_decode($respAjax->getBody(), true);
assert($json['status'] === 'error' && $json['success'] === false, "FAIL: Expected JSON error response");

echo "  ✓ PASS: AJAX POST without token rejected with HTTP 419 JSON.\n\n";

// --------------------------------------------------------------------------
// TEST 3: POST request with valid _csrf in body succeeds
// --------------------------------------------------------------------------
echo "[TEST 3] Testing POST with valid _csrf in body -> Must succeed (return null)...\n";

$reqWithBodyToken = new Request([], ['_csrf' => $validToken, 'city' => 'Kashipur'], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/api/service-areas',
]);

$respBody = $middleware->handle($reqWithBodyToken);
assert($respBody === null, "FAIL: Request with valid _csrf body token should be allowed, got response");

echo "  ✓ PASS: Request with valid _csrf body token allowed.\n\n";

// --------------------------------------------------------------------------
// TEST 4: AJAX request with X-CSRF-TOKEN header succeeds
// --------------------------------------------------------------------------
echo "[TEST 4] Testing AJAX request with X-CSRF-TOKEN header -> Must succeed...\n";

$reqWithHeader = new Request([], ['service_id' => '1'], [
    'REQUEST_METHOD'     => 'POST',
    'REQUEST_URI'        => '/api/cart/add',
    'HTTP_X_CSRF_TOKEN'  => $validToken,
]);

$respHeader = $middleware->handle($reqWithHeader);
assert($respHeader === null, "FAIL: Request with valid X-CSRF-TOKEN header should be allowed, got response");

echo "  ✓ PASS: AJAX request with valid X-CSRF-TOKEN header allowed.\n\n";

// --------------------------------------------------------------------------
// TEST 5: AJAX request with X-XSRF-TOKEN header succeeds
// --------------------------------------------------------------------------
echo "[TEST 5] Testing AJAX request with X-XSRF-TOKEN header -> Must succeed...\n";

$reqWithXsrfHeader = new Request([], ['service_id' => '2'], [
    'REQUEST_METHOD'     => 'POST',
    'REQUEST_URI'        => '/api/cart/add',
    'HTTP_X_XSRF_TOKEN'  => $validToken,
]);

$respXsrfHeader = $middleware->handle($reqWithXsrfHeader);
assert($respXsrfHeader === null, "FAIL: Request with valid X-XSRF-TOKEN header should be allowed, got response");

echo "  ✓ PASS: AJAX request with valid X-XSRF-TOKEN header allowed.\n\n";

// --------------------------------------------------------------------------
// TEST 6: Webhook /webhook/razorpay is exempt from CSRF
// --------------------------------------------------------------------------
echo "[TEST 6] Testing /webhook/razorpay exemption from CSRF...\n";

$webhookReq = new Request([], ['event' => 'payment.captured'], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/webhook/razorpay',
]);

$webhookResp = $middleware->handle($webhookReq);
assert($webhookResp === null, "FAIL: /webhook/razorpay must be exempt from CSRF validation!");

echo "  ✓ PASS: /webhook/razorpay is exempt from CSRF.\n\n";

// --------------------------------------------------------------------------
// TEST 7: Safe HTTP GET method does not require CSRF token
// --------------------------------------------------------------------------
echo "[TEST 7] Testing GET request does not require CSRF token...\n";

$getReq = new Request([], [], [
    'REQUEST_METHOD' => 'GET',
    'REQUEST_URI'    => '/contact',
]);

$getResp = $middleware->handle($getReq);
assert($getResp === null, "FAIL: GET requests must not require CSRF token!");

echo "  ✓ PASS: GET requests bypass CSRF validation.\n\n";

echo "========================================================\n";
echo "   ALL CSRF PROTECTION TESTS PASSED! ✓                  \n";
echo "========================================================\n";
