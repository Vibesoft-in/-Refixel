<?php
declare(strict_types=1);

namespace Tests\Feature;

require_once __DIR__ . '/../../tests/bootstrap.php';

use App\Core\Env;
use App\Core\Request;
use App\Core\Response;

class HttpsAndSecurityHeadersTest
{
    public function runAll(): void
    {
        echo "========================================================\n";
        echo "   TEST SUITE: HTTPS & SECURITY HEADERS (CSP / HSTS)    \n";
        echo "========================================================\n\n";

        $this->testHttpsRedirectAndLoopPrevention();
        $this->testHstsWithoutPreload();
        $this->testCspReportOnlyModeAndImgSrc();
        $this->testBaselineSecurityHeaders();

        echo "\n========================================================\n";
        echo "   ALL HTTPS & SECURITY HEADERS TESTS PASSED! ✓         \n";
        echo "========================================================\n";
    }

    private function assert(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new \RuntimeException("ASSERTION FAILED: {$message}");
        }
        echo "  ✓ PASS: {$message}\n";
    }

    /**
     * Test 1: Verify 301 HTTPS redirect and redirect loop prevention behind trusted proxies
     */
    public function testHttpsRedirectAndLoopPrevention(): void
    {
        echo "[TEST 1] Testing FORCE_HTTPS redirect and loop prevention behind proxies...\n";

        $oldForce = Env::get('FORCE_HTTPS');
        $oldTrusted = Env::get('TRUSTED_PROXIES');

        try {
            Env::set('FORCE_HTTPS', true);
            Env::set('TRUSTED_PROXIES', '10.0.0.1,10.0.0.2');

            // 1. Direct HTTP request (no HTTPS, no proxy) -> Must trigger 301 redirect
            $_SERVER['HTTPS'] = 'off';
            $_SERVER['REMOTE_ADDR'] = '203.0.113.10';
            unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
            $isHttpsDirectHttp = Request::isHttpsConnection($_SERVER);
            $this->assert($isHttpsDirectHttp === false, "Direct HTTP recognized as insecure (requires 301 redirect)");

            // 2. Direct HTTPS request -> Must NOT redirect
            $_SERVER['HTTPS'] = 'on';
            $isHttpsDirectHttps = Request::isHttpsConnection($_SERVER);
            $this->assert($isHttpsDirectHttps === true, "Direct HTTPS connection allowed without redirect loop");

            // 3. Trusted proxy request with X-Forwarded-Proto: https -> Must NOT redirect (prevents infinite loop!)
            $_SERVER['HTTPS'] = 'off';
            $_SERVER['REMOTE_ADDR'] = '10.0.0.1'; // in TRUSTED_PROXIES
            $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
            $isHttpsTrustedProxy = Request::isHttpsConnection($_SERVER);
            $this->assert($isHttpsTrustedProxy === true, "Trusted proxy with X-Forwarded-Proto: https recognized (NO redirect loop)");

            // 4. Untrusted proxy/client sending spoofed X-Forwarded-Proto: https -> Must be IGNORED
            $_SERVER['HTTPS'] = 'off';
            $_SERVER['REMOTE_ADDR'] = '198.51.100.99'; // NOT in TRUSTED_PROXIES
            $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
            $isHttpsUntrustedProxy = Request::isHttpsConnection($_SERVER);
            $this->assert($isHttpsUntrustedProxy === false, "Untrusted proxy spoofed X-Forwarded-Proto is ignored (triggers 301 redirect to real HTTPS)");
        } finally {
            Env::set('FORCE_HTTPS', $oldForce);
            Env::set('TRUSTED_PROXIES', $oldTrusted);
            unset($_SERVER['HTTPS'], $_SERVER['HTTP_X_FORWARDED_PROTO']);
        }
    }

    /**
     * Test 2: Verify HSTS header without preload (only when HTTPS)
     */
    public function testHstsWithoutPreload(): void
    {
        echo "\n[TEST 2] Testing Strict-Transport-Security (HSTS) without preload...\n";

        // When insecure: No HSTS
        $_SERVER['HTTPS'] = 'off';
        unset($_SERVER['HTTP_X_FORWARDED_PROTO']);
        $headersInsecure = Response::buildSecurityHeaders();
        $this->assert(!isset($headersInsecure['Strict-Transport-Security']), "Insecure HTTP requests do NOT send Strict-Transport-Security");

        // When HTTPS: HSTS present
        $_SERVER['HTTPS'] = 'on';
        $headersSecure = Response::buildSecurityHeaders();
        $this->assert(isset($headersSecure['Strict-Transport-Security']), "HTTPS requests send Strict-Transport-Security header");

        $hsts = $headersSecure['Strict-Transport-Security'];
        $this->assert(str_contains($hsts, 'max-age=31536000'), "HSTS max-age is set to 1 year (31536000 seconds)");
        $this->assert(str_contains($hsts, 'includeSubDomains'), "HSTS includes includeSubDomains directive");
        $this->assert(!str_contains($hsts, 'preload'), "HSTS strictly does NOT contain preload directive (per user instructions)");

        unset($_SERVER['HTTPS']);
    }

    /**
     * Test 3: Verify Content-Security-Policy (CSP) with Report-Only mode and img-src
     */
    public function testCspReportOnlyModeAndImgSrc(): void
    {
        echo "\n[TEST 3] Testing CSP configuration and Report-Only mode...\n";

        $oldReportOnly = Env::get('CSP_REPORT_ONLY');
        try {
            // Case A: Report-Only mode active (default)
            Env::set('CSP_REPORT_ONLY', true);
            $headers = Response::buildSecurityHeaders();

            $this->assert(isset($headers['Content-Security-Policy-Report-Only']), "CSP Report-Only header present when CSP_REPORT_ONLY=true");
            $this->assert(!isset($headers['Content-Security-Policy']), "Enforcing CSP header is NOT active in Report-Only mode");

            $policy = $headers['Content-Security-Policy-Report-Only'];

            // Directive assertions
            $this->assert(str_contains($policy, "img-src 'self' data: https:"), "CSP img-src includes 'self' data: https:");
            $this->assert(str_contains($policy, "https://checkout.razorpay.com"), "CSP script-src includes Razorpay checkout");
            $this->assert(str_contains($policy, "https://api.razorpay.com"), "CSP includes Razorpay API domains");
            $this->assert(str_contains($policy, "https://fonts.googleapis.com"), "CSP style-src includes Google Fonts");
            $this->assert(str_contains($policy, "https://fonts.gstatic.com"), "CSP font-src includes Google Fonts gstatic");

            // Case B: Enforcing mode
            Env::set('CSP_REPORT_ONLY', false);
            $enforceHeaders = Response::buildSecurityHeaders();
            $this->assert(isset($enforceHeaders['Content-Security-Policy']), "Enforcing Content-Security-Policy header present when CSP_REPORT_ONLY=false");
            $this->assert(!isset($enforceHeaders['Content-Security-Policy-Report-Only']), "Report-Only header absent in enforcing mode");
        } finally {
            Env::set('CSP_REPORT_ONLY', $oldReportOnly);
        }
    }

    /**
     * Test 4: Verify baseline hardening headers
     */
    public function testBaselineSecurityHeaders(): void
    {
        echo "\n[TEST 4] Testing baseline security headers...\n";

        $headers = Response::buildSecurityHeaders();

        $this->assert($headers['X-Content-Type-Options'] === 'nosniff', "X-Content-Type-Options is nosniff");
        $this->assert($headers['X-Frame-Options'] === 'SAMEORIGIN', "X-Frame-Options is SAMEORIGIN");
        $this->assert($headers['Referrer-Policy'] === 'strict-origin-when-cross-origin', "Referrer-Policy is strict-origin-when-cross-origin");
        $this->assert(str_contains($headers['Permissions-Policy'], 'camera=()'), "Permissions-Policy restricts camera");
        $this->assert(str_contains($headers['Permissions-Policy'], 'microphone=()'), "Permissions-Policy restricts microphone");
        $this->assert(str_contains($headers['Permissions-Policy'], 'geolocation=(self)'), "Permissions-Policy restricts geolocation to self");
    }
}

// Run test suite
$test = new HttpsAndSecurityHeadersTest();
$test->runAll();
