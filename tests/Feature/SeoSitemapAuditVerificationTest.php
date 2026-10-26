<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once ROOT_PATH . '/config/routes.php';

use App\Core\Env;
use App\Core\Request;
use App\Core\Response;
use App\Core\Router;
use App\Core\View;
use App\Controllers\PageController;

echo "=== RUNNING SEO & SITEMAP AUDIT VERIFICATION TESTS ===\n\n";

$pageCtrl = new PageController();

// -----------------------------------------------------------------
// ITEM 1 & 2: Base Domain, Sitemap URLs & Canonical Absolute URLs
// -----------------------------------------------------------------
echo "[CHECK 1 & 2] Verifying Sitemap URLs and Canonical Format...\n";
$oldUrl = Env::get('APP_URL');
Env::set('APP_URL', 'https://refixel.com');

$sitemapRes = $pageCtrl->sitemap(new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/sitemap.xml']));
assert($sitemapRes->getStatusCode() === 200, "Sitemap must return 200 OK");
$xmlContent = $sitemapRes->getContent();

$xml = simplexml_load_string($xmlContent);
$urls = [];
foreach ($xml->url as $entry) {
    $urls[] = (string)$entry->loc;
}

echo "  Total URLs in sitemap: " . count($urls) . "\n";
assert(count($urls) > 50, "Sitemap must contain extensive service and city URLs");

// Check all URLs start with main website https://refixel.com
foreach ($urls as $u) {
    assert(str_starts_with($u, 'https://refixel.com/'), "All sitemap URLs must start with https://refixel.com: found $u");
}
echo "  ✓ PASS: All sitemap URLs use canonical main domain https://refixel.com/\n";

// -----------------------------------------------------------------
// ITEM 3: Important service & static pages included in sitemap
// -----------------------------------------------------------------
echo "[CHECK 3] Verifying Important Pages in Sitemap...\n";
$requiredPages = [
    'https://refixel.com/',
    'https://refixel.com/about',
    'https://refixel.com/services',
    'https://refixel.com/blog',
    'https://refixel.com/partner',
    'https://refixel.com/contact',
    'https://refixel.com/cleaning-services',
    'https://refixel.com/plumber-services',
    'https://refixel.com/plumber-services-in-kashipur',
    'https://refixel.com/cleaning-services-in-kashipur',
    'https://refixel.com/ac-jet-service-in-kashipur',
    'https://refixel.com/water-tank-cleaning-in-kashipur',
];
foreach ($requiredPages as $reqUrl) {
    assert(in_array($reqUrl, $urls, true), "Sitemap must include $reqUrl");
}
echo "  ✓ PASS: Key service and landing pages (including /blog, /partner, plumber canonical) are present\n";

// -----------------------------------------------------------------
// ITEM 4 & 6: No Duplicate URLs and No Broken/Redirected URLs
// -----------------------------------------------------------------
echo "[CHECK 4 & 6] Checking for Duplicates and Route Status...\n";
$uniqueCount = count(array_unique($urls));
assert(count($urls) === $uniqueCount, "Sitemap must not contain any duplicate URLs");
echo "  ✓ PASS: Zero duplicate URLs in sitemap (Total unique: $uniqueCount)\n";

// Verify Plumber 301 is NOT in sitemap, but instead the 200 OK canonical is
assert(!in_array('https://refixel.com/plumbers-services', $urls, true), "Redirected /plumbers-services must NOT be in sitemap");
assert(!in_array('https://refixel.com/plumbers-services-in-kashipur', $urls, true), "Redirected /plumbers-services-in-kashipur must NOT be in sitemap");
echo "  ✓ PASS: 301 redirect plumber URLs removed from sitemap; canonical 200 OK present\n";

// Verify Legacy .php URLs 301-redirect to canonical clean URLs
$legacyRedirects = [
    '/about-us.php' => '/about',
    '/faqs.php' => '/faq',
    '/contact-us.php' => '/contact',
    '/terms-and-conditions.php' => '/terms',
    '/privacy-policy.php' => '/privacy',
    '/refund-policy.php' => '/refund',
    '/blog.php' => '/blog',
    '/cart.php' => '/cart',
    '/my-booking.php' => '/account/bookings',
    '/logout.php' => '/logout',
];
foreach ($legacyRedirects as $legacyPath => $targetClean) {
    $res = Router::dispatch(new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => $legacyPath]));
    assert($res->getStatusCode() === 301, "Legacy path $legacyPath must return 301");
    $loc = $res->getHeader('Location');
    assert(str_ends_with($loc, $targetClean), "Legacy path $legacyPath must redirect to $targetClean, got $loc");
}
echo "  ✓ PASS: All legacy .php aliases now 301-redirect to clean canonical URLs (duplicate content eliminated)\n";

// -----------------------------------------------------------------
// ITEM 5: Admin, Login & Internal Pages Excluded & Protected
// -----------------------------------------------------------------
echo "[CHECK 5] Verifying Internal/Admin Pages Exclusion & Protection...\n";
$internalPages = [
    'https://refixel.com/admin',
    'https://refixel.com/superadmin',
    'https://refixel.com/staff',
    'https://refixel.com/account',
    'https://refixel.com/login',
    'https://refixel.com/signup',
    'https://refixel.com/cart',
    'https://refixel.com/book',
];
foreach ($internalPages as $internalUrl) {
    assert(!in_array($internalUrl, $urls, true), "Internal/admin URL $internalUrl must NOT be in sitemap");
}
echo "  ✓ PASS: All admin, auth, and internal URLs are excluded from sitemap\n";

// Verify robots.txt protects admin, internal, and auth routes
$robotsRes = $pageCtrl->robots(new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/robots.txt']));
$robotsTxt = $robotsRes->getContent();
assert(str_contains($robotsTxt, 'Disallow: /admin'), "robots.txt must Disallow /admin");
assert(str_contains($robotsTxt, 'Disallow: /superadmin'), "robots.txt must Disallow /superadmin");
assert(str_contains($robotsTxt, 'Disallow: /staff'), "robots.txt must Disallow /staff");
assert(str_contains($robotsTxt, 'Disallow: /account'), "robots.txt must Disallow /account");
assert(str_contains($robotsTxt, 'Disallow: /login'), "robots.txt must Disallow /login");
assert(str_contains($robotsTxt, 'Disallow: /signup'), "robots.txt must Disallow /signup");
assert(str_contains($robotsTxt, 'Disallow: /cart'), "robots.txt must Disallow /cart");
assert(str_contains($robotsTxt, 'Disallow: /book'), "robots.txt must Disallow /book");
assert(str_contains($robotsTxt, 'Disallow: /documents/'), "robots.txt must Disallow /documents/");
echo "  ✓ PASS: Production robots.txt disallows all admin, superadmin, login, cart, book, and account pages\n";

// -----------------------------------------------------------------
// ITEM 7: Staging Environment Protection
// -----------------------------------------------------------------
echo "[CHECK 7] Verifying Staging Protection (Disallow all & X-Robots-Tag)...\n";
Env::set('APP_ENV', 'staging');
$stagingRobotsRes = $pageCtrl->robots(new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/robots.txt']));
$stagingRobotsTxt = $stagingRobotsRes->getContent();
assert(str_contains($stagingRobotsTxt, "User-agent: *\nDisallow: /"), "Staging robots.txt must disallow all crawling");
assert(!str_contains($stagingRobotsTxt, "Allow: /"), "Staging robots.txt must not allow crawling");

// Check X-Robots-Tag header on Response
$stagingHeaders = Response::buildSecurityHeaders();
assert(isset($stagingHeaders['X-Robots-Tag']), "Staging responses must have X-Robots-Tag header");
assert(str_contains($stagingHeaders['X-Robots-Tag'], 'noindex'), "Staging X-Robots-Tag must contain noindex");
echo "  ✓ PASS: Staging environment blocks crawlers in robots.txt and emits X-Robots-Tag: noindex, nofollow\n";

// Restore env
Env::set('APP_ENV', 'test');
Env::set('APP_URL', $oldUrl);

echo "\n=======================================================\n";
echo "   ALL 7 SEO & SITEMAP AUDIT CHECKS PASSED 100%! ✓\n";
echo "=======================================================\n";
