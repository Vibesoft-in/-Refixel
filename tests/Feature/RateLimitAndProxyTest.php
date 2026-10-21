<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Database;
use App\Core\Env;
use App\Core\Request;
use App\Core\Response;
use App\Middleware\RateLimit;

echo "========================================================\n";
echo "   TEST SUITE: RATE LIMITING & TRUSTED PROXY HARDENING \n";
echo "========================================================\n\n";

// --------------------------------------------------------------------------
// TEST 1: Rate limiting triggers without session cookie (Stateless / DB-based)
// --------------------------------------------------------------------------
echo "[TEST 1] Testing DB-backed rate limiting without session cookie...\n";

$testIp = '192.0.2.77';
Database::query("TRUNCATE TABLE rate_limits");

$middleware = new RateLimit();
$limitArg = '2,60'; // Max 2 attempts per 60 seconds

// Request 1: Should pass
$req1 = new Request([], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/login',
    'REMOTE_ADDR'    => $testIp,
]);
$resp1 = $middleware->handle($req1, $limitArg);
assert($resp1 === null, "FAIL: First request should be allowed (returned null), got response");

// Request 2: Should pass
$req2 = new Request([], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/login',
    'REMOTE_ADDR'    => $testIp,
]);
$resp2 = $middleware->handle($req2, $limitArg);
assert($resp2 === null, "FAIL: Second request should be allowed (returned null), got response");

// Request 3: Must trigger HTTP 429
$req3 = new Request([], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/login',
    'REMOTE_ADDR'    => $testIp,
]);
$resp3 = $middleware->handle($req3, $limitArg);
assert($resp3 instanceof Response, "FAIL: Third request must return a Response object");
assert($resp3->getStatusCode() === 429, "FAIL: Expected HTTP 429, got " . $resp3->getStatusCode());
$retryAfter = $resp3->getHeader('Retry-After');
assert(!empty($retryAfter) && (int)$retryAfter > 0, "FAIL: Missing or invalid Retry-After header: " . var_export($retryAfter, true));

echo "  ✓ PASS: Rate limiting triggers HTTP 429 with Retry-After ({$retryAfter}s) without session cookies.\n\n";

// --------------------------------------------------------------------------
// TEST 2: Untrusted X-Forwarded-For IP Spoofing Prevention
// --------------------------------------------------------------------------
echo "[TEST 2] Testing untrusted X-Forwarded-For spoofing prevention...\n";

// Configure trusted proxies to only allow localhost
Env::set('TRUSTED_PROXIES', '127.0.0.1,::1');

// An attacker connecting from an untrusted public IP sends fake headers
$attackerReq = new Request([], [], [
    'REQUEST_METHOD'          => 'POST',
    'REQUEST_URI'             => '/login',
    'REMOTE_ADDR'             => '198.51.100.99', // Untrusted attacker IP
    'HTTP_X_FORWARDED_FOR'    => '203.0.113.1, 10.0.0.1', // Fake client IP
    'HTTP_CF_CONNECTING_IP'   => '203.0.113.2', // Fake Cloudflare IP
    'HTTP_CLIENT_IP'          => '203.0.113.3',
]);

assert(
    $attackerReq->getIp() === '198.51.100.99',
    "FAIL: Request accepted spoofed IP! Expected '198.51.100.99', got: " . $attackerReq->getIp()
);

echo "  ✓ PASS: Untrusted X-Forwarded-For & CF-Connecting-IP strictly ignored, remote IP preserved.\n\n";

// --------------------------------------------------------------------------
// TEST 3: Trusted Proxy IP Resolution (Cloudflare / Internal Proxy)
// --------------------------------------------------------------------------
echo "[TEST 3] Testing trusted proxy IP resolution (Single IP and CIDR)...\n";

// Allow local proxy and private 10.0.0.0/8 CIDR as trusted proxies
Env::set('TRUSTED_PROXIES', '127.0.0.1, 10.0.0.0/8, 172.16.0.0/12');

// Case A: Cloudflare behind trusted proxy
$cfReq = new Request([], [], [
    'REQUEST_METHOD'        => 'GET',
    'REQUEST_URI'           => '/',
    'REMOTE_ADDR'           => '10.0.5.20', // In trusted CIDR 10.0.0.0/8
    'HTTP_CF_CONNECTING_IP' => '103.21.244.5',
]);
assert(
    $cfReq->getIp() === '103.21.244.5',
    "FAIL: Trusted proxy failed to extract CF-Connecting-IP, got: " . $cfReq->getIp()
);

// Case B: Standard X-Forwarded-For with multiple hops through trusted proxy
$xffReq = new Request([], [], [
    'REQUEST_METHOD'       => 'GET',
    'REQUEST_URI'          => '/',
    'REMOTE_ADDR'          => '127.0.0.1', // Trusted local reverse proxy
    'HTTP_X_FORWARDED_FOR' => '157.240.22.35, 10.0.0.1',
]);
assert(
    $xffReq->getIp() === '157.240.22.35',
    "FAIL: Trusted proxy failed to extract leftmost client IP from XFF, got: " . $xffReq->getIp()
);

echo "  ✓ PASS: Trusted proxies correctly resolve real client IPs across Cloudflare and XFF chains.\n\n";

// --------------------------------------------------------------------------
// TEST 4: Protocol & HTTPS Detection - Prevent Redirect Loops
// --------------------------------------------------------------------------
echo "[TEST 4] Testing X-Forwarded-Proto trust verification...\n";

// Case A: Untrusted client claims HTTPS via header -> MUST BE REJECTED
$untrustedProtoReq = new Request([], [], [
    'REQUEST_METHOD'         => 'GET',
    'REQUEST_URI'            => '/account',
    'REMOTE_ADDR'            => '198.51.100.45', // Untrusted
    'HTTP_X_FORWARDED_PROTO' => 'https',
]);
assert(
    $untrustedProtoReq->isHttps() === false,
    "FAIL: Untrusted client forged X-Forwarded-Proto: https, but isHttps() returned true!"
);

// Case B: Trusted reverse proxy reports HTTPS -> MUST BE ACCEPTED
$trustedProtoReq = new Request([], [], [
    'REQUEST_METHOD'         => 'GET',
    'REQUEST_URI'            => '/account',
    'REMOTE_ADDR'            => '127.0.0.1', // Trusted
    'HTTP_X_FORWARDED_PROTO' => 'https',
]);
assert(
    $trustedProtoReq->isHttps() === true,
    "FAIL: Trusted proxy reported HTTPS, but isHttps() returned false!"
);

echo "  ✓ PASS: X-Forwarded-Proto only honored from trusted proxies, preventing HTTPS spoofing/loops.\n\n";

// --------------------------------------------------------------------------
// TEST 5: Razorpay Webhook Exemption from Rate Limiting
// --------------------------------------------------------------------------
echo "[TEST 5] Testing /webhook/razorpay rate limit exemption...\n";

$webhookReq = new Request([], [], [
    'REQUEST_METHOD' => 'POST',
    'REQUEST_URI'    => '/webhook/razorpay',
    'REMOTE_ADDR'    => '100.50.25.10',
]);

// Send 50 requests rapidly
for ($i = 0; $i < 50; $i++) {
    $res = $middleware->handle($webhookReq, '5,60');
    assert($res === null, "FAIL: Webhook route was rate-limited on iteration {$i}!");
}

echo "  ✓ PASS: /webhook/razorpay is completely exempt from rate limiting.\n\n";

// --------------------------------------------------------------------------
// TEST 6: Expired Rate Limits Database Cleanup
// --------------------------------------------------------------------------
echo "[TEST 6] Testing expired rate limits cleanup...\n";

$now = time();
$oldKey1 = 'rl_test_expired_1_' . bin2hex(random_bytes(6));
$oldKey2 = 'rl_test_expired_2_' . bin2hex(random_bytes(6));
$activeKey = 'rl_test_active_' . bin2hex(random_bytes(6));

// Insert 2 expired records and 1 active record
Database::query("INSERT INTO rate_limits (rate_key, attempts, reset_at) VALUES (:k1, 5, :old1), (:k2, 3, :old2), (:k3, 1, :act)", [
    'k1'   => $oldKey1,
    'old1' => $now - 120, // Expired 2 minutes ago
    'k2'   => $oldKey2,
    'old2' => $now - 30,  // Expired 30s ago
    'k3'   => $activeKey,
    'act'  => $now + 60,  // Active for 60s
]);

$deletedCount = RateLimit::cleanupExpired($now);
assert($deletedCount >= 2, "FAIL: Expected at least 2 expired records deleted, got {$deletedCount}");

$checkActive = Database::fetchOne("SELECT * FROM rate_limits WHERE rate_key = :k", ['k' => $activeKey]);
assert($checkActive !== null, "FAIL: Active rate limit was mistakenly deleted!");

echo "  ✓ PASS: Expired rate limits cleaned up successfully ({$deletedCount} purged, active preserved).\n\n";

// Cleanup test records
Database::query("DELETE FROM rate_limits WHERE rate_key LIKE 'rl_test_%' OR rate_key LIKE '%{$testIp}%'");

echo "========================================================\n";
echo "   ALL RATE LIMITING & PROXY TESTS PASSED! ✓            \n";
echo "========================================================\n";
