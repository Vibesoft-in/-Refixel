<?php
declare(strict_types=1);

namespace Tests\Feature;

require __DIR__ . '/../bootstrap.php';

use App\Controllers\Admin\ContentController;
use App\Controllers\Admin\SettingsController;
use App\Controllers\HomeController;
use App\Controllers\PageController;
use App\Controllers\ServiceController;
use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\ProcessStep;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceArea;
use App\Models\Setting;

echo "=== RUNNING WEBSITE CONTENT, LOCAL SEO & CMS FEATURE TESTS (PROMPT 12) ===\n";

$adminContentCtrl = new ContentController();
$adminSettingsCtrl = new SettingsController();
$pageCtrl = new PageController();
$homeCtrl = new HomeController();
$serviceCtrl = new ServiceController();

// Authenticate as Admin
$admin = Database::fetchOne("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
assert($admin !== null, "Admin user must exist");
Auth::login($admin);

// ==========================================
// TEST 1: FAQs CMS Operations (Global & Per-Service)
// ==========================================
$faqReq = new Request([
    'question'   => 'What certifications do Primodomus technicians hold?',
    'answer'     => 'All technicians hold verified vocational skill certificates and pass identity verification.',
    'service_id' => 0,
    'sort_order' => 10,
], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/admin/content/faqs']);

$faqRes = $adminContentCtrl->storeFaq($faqReq);
assert($faqRes->getStatusCode() === 302, "Store FAQ must redirect");

$storedFaq = Database::fetchOne(
    "SELECT * FROM faqs WHERE question LIKE '%certifications do Primodomus%' ORDER BY id DESC LIMIT 1"
);
assert($storedFaq !== null, "FAQ must be stored in database");
assert((int)$storedFaq['is_active'] === 1, "FAQ must be active by default");

// Delete FAQ
$delFaqRes = $adminContentCtrl->deleteFaq(new Request(), (string)$storedFaq['id']);
assert($delFaqRes->getStatusCode() === 302, "Delete FAQ must redirect");

$deletedFaq = Database::fetchOne("SELECT * FROM faqs WHERE id = :id", ['id' => $storedFaq['id']]);
assert($deletedFaq === null, "FAQ must be removed from database");
echo "Test 1: FAQs CMS Operations - PASSED\n";

// ==========================================
// TEST 2: Service Checklists CMS Operations (Inclusions / Exclusions)
// ==========================================
$firstService = Service::all('id ASC')[0] ?? null;
assert($firstService !== null, "At least one service must exist");

$chkReq = new Request([
    'service_id'  => (int)$firstService['id'],
    'label'       => 'Deep high-temperature steam sanitization',
    'is_included' => 1,
    'sort_order'  => 5,
], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/admin/content/checklists']);

$chkRes = $adminContentCtrl->storeChecklist($chkReq);
assert($chkRes->getStatusCode() === 302, "Store checklist must redirect");

$storedItem = Database::fetchOne(
    "SELECT * FROM service_checklist_items WHERE label = 'Deep high-temperature steam sanitization' LIMIT 1"
);
assert($storedItem !== null, "Checklist item must be saved in database");
assert((int)$storedItem['is_included'] === 1, "Item must be marked as included");

// Cleanup checklist item
$delChkRes = $adminContentCtrl->deleteChecklist(new Request(), (string)$storedItem['id']);
assert($delChkRes->getStatusCode() === 302, "Delete checklist must redirect");
$deletedItem = Database::fetchOne("SELECT * FROM service_checklist_items WHERE id = :id", ['id' => $storedItem['id']]);
assert($deletedItem === null, "Checklist item must be deleted");
echo "Test 2: Service Checklists CMS Operations - PASSED\n";

// ==========================================
// TEST 3: Service Areas & Pincodes CMS Operations
// ==========================================
Database::query("DELETE FROM service_areas WHERE area_name = 'Kashipur Station Road Area'");

$areaReq = new Request([
    'city'    => 'Kashipur',
    'pincode' => '244713',
    'name'    => 'Kashipur Station Road Area',
], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/admin/content/areas']);

$areaRes = $adminContentCtrl->storeArea($areaReq);
assert($areaRes->getStatusCode() === 302, "Store area must redirect");

$storedArea = Database::fetchOne(
    "SELECT * FROM service_areas WHERE pincode = '244713' AND area_name = 'Kashipur Station Road Area' LIMIT 1"
);
assert($storedArea !== null, "Service area must be saved");
assert((int)$storedArea['is_active'] === 1, "Service area should start as active (1)");

// Toggle Status
$toggleAreaRes = $adminContentCtrl->toggleArea(new Request(), (string)$storedArea['id']);
assert($toggleAreaRes->getStatusCode() === 302, "Toggle area status must redirect");

$toggledArea = ServiceArea::find((int)$storedArea['id']);
assert((int)$toggledArea['is_active'] === 0, "Service area status must be toggled to 0");

// Teardown
Database::query("DELETE FROM service_areas WHERE id = :id", ['id' => $storedArea['id']]);
echo "Test 3: Service Areas & Pincodes CMS Operations - PASSED\n";

// ==========================================
// TEST 4: Process Steps CMS Operations
// ==========================================
$step = ProcessStep::all('step_no ASC')[0] ?? null;
assert($step !== null, "Process step must exist in database");

$stepUpdateReq = new Request([
    'title'       => 'Step 1: Effortless Online Booking',
    'description' => 'Select your service, choose preferred slot, and get instant verified technician assignment.',
], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/admin/content/steps/{$step['id']}"]);

$stepRes = $adminContentCtrl->updateStep($stepUpdateReq, (string)$step['id']);
assert($stepRes->getStatusCode() === 302, "Update step must redirect");

$updatedStep = ProcessStep::find((int)$step['id']);
assert($updatedStep['title'] === 'Step 1: Effortless Online Booking', "Process step title must be updated");
echo "Test 4: Process Steps CMS Operations - PASSED\n";

// ==========================================
// TEST 5: Business Settings & Dynamic Stats
// ==========================================
$settingsReq = new Request([
    'promo_strip_text' => 'Festival Special • Flat 20% off all deep cleaning services',
    'stat_rating'      => '4.9★',
    'stat_rating_note' => 'Based on 1500+ Verified Doorstep Services',
    'company_phone'    => '+91 99533 58855',
], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/admin/settings']);

$settingsRes = $adminSettingsCtrl->save($settingsReq);
assert($settingsRes->getStatusCode() === 302, "Settings save must redirect");

assert(Setting::get('promo_strip_text') === 'Festival Special • Flat 20% off all deep cleaning services', "Promo strip setting must be updated");
assert(Setting::get('stat_rating') === '4.9★', "Stat rating setting must be updated");

// Test that public about view renders updated settings
$aboutRes = $pageCtrl->about(new Request());
assert($aboutRes->getStatusCode() === 200, "About page must return 200");
assert(str_contains($aboutRes->getContent(), '4.9★'), "About page must render updated stat rating");
echo "Test 5: Business Settings & Dynamic Stats - PASSED\n";

// ==========================================
// TEST 6: Public Sitemap.xml Generation
// ==========================================
$baseUrl = rtrim((string)\App\Core\Env::get('APP_URL', 'https://www.primodomus.com'), '/');

$sitemapReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/sitemap.xml']);
$sitemapRes = $pageCtrl->sitemap($sitemapReq);
assert($sitemapRes->getStatusCode() === 200, "Sitemap must return 200 OK");

$xmlContent = $sitemapRes->getContent();
assert(str_contains($xmlContent, '<?xml version="1.0" encoding="UTF-8"?>'), "Sitemap must have XML declaration");
assert(str_contains($xmlContent, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'), "Sitemap must have valid urlset namespace");
assert(str_contains($xmlContent, "<loc>{$baseUrl}/</loc>"), "Sitemap must include root URL");
assert(str_contains($xmlContent, "<loc>{$baseUrl}/about</loc>"), "Sitemap must include about page");
assert(str_contains($xmlContent, "<loc>{$baseUrl}/services</loc>"), "Sitemap must include services page");
// Check for city category URLs in sitemap
assert(str_contains($xmlContent, '-services-in-'), "Sitemap must contain dynamic clean city service/category URLs");
echo "Test 6: Public Sitemap.xml Generation - PASSED\n";

// ==========================================
// TEST 7: Public Robots.txt Generation
// ==========================================
$robotsReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/robots.txt']);
$robotsRes = $pageCtrl->robots($robotsReq);
assert($robotsRes->getStatusCode() === 200, "Robots.txt must return 200 OK");

$txtContent = $robotsRes->getContent();
assert(str_contains($txtContent, 'User-agent: *'), "Robots.txt must contain User-agent");
assert(str_contains($txtContent, 'Disallow: /admin'), "Robots.txt must protect /admin");
assert(str_contains($txtContent, 'Disallow: /staff'), "Robots.txt must protect /staff");
assert(str_contains($txtContent, 'Disallow: /account'), "Robots.txt must protect /account");
assert(str_contains($txtContent, 'Allow: /'), "Robots.txt must allow root public crawling");
assert(str_contains($txtContent, "Sitemap: {$baseUrl}/sitemap.xml"), "Robots.txt must reference sitemap.xml");
echo "Test 7: Public Robots.txt Generation - PASSED\n";

// ==========================================
// TEST 8: LocalBusiness Structured Schema on Public Pages
// ==========================================
Auth::logout(); // Test public guest view
$homeRes = $homeCtrl->index(new Request());
assert($homeRes->getStatusCode() === 200, "Homepage must return 200");
$homeHtml = $homeRes->getContent();
assert(str_contains($homeHtml, 'application/ld+json'), "Homepage must include JSON-LD script");
assert(str_contains($homeHtml, 'HomeAndConstructionBusiness') || str_contains($homeHtml, 'LocalBusiness'), "Schema must include business type");
assert(str_contains($homeHtml, 'OpeningHoursSpecification'), "Schema must include opening hours specification");
echo "Test 8: LocalBusiness Structured Schema on Public Pages - PASSED\n";

// ==========================================
// TEST 9: Category & Service Clean URLs Schema Integration
// ==========================================
$catReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/cleaning-services-in-kashipur']);
$catRes = $serviceCtrl->categoryInCity($catReq, 'cleaning', 'kashipur');
assert($catRes->getStatusCode() === 200, "Category in city must return 200");
assert(str_contains($catRes->getContent(), 'application/ld+json'), "Category page must contain JSON-LD schema");
assert(str_contains($catRes->getContent(), 'BreadcrumbList'), "Category page must contain BreadcrumbList schema");

// Authenticate session for service page test
$_SESSION['user'] = [
    'id'                   => 1,
    'role'                 => 'customer',
    'name'                 => 'Test Customer',
    'email'                => 'customer@refixel.com',
    'must_change_password' => false,
];
$_SESSION['last_activity'] = time();

$svcReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/full-home-cleaning-in-kashipur']);
$svcRes = $serviceCtrl->serviceInCity($svcReq, 'full-home-cleaning', 'kashipur');
assert($svcRes->getStatusCode() === 200, "Service in city must return 200");
assert(str_contains($svcRes->getContent(), 'application/ld+json'), "Service page must contain JSON-LD schema");
assert(str_contains($svcRes->getContent(), 'Service') || str_contains($svcRes->getContent(), 'offers'), "Service page schema must contain service/offers data");
echo "Test 9: Category & Service Clean URLs Schema Integration - PASSED\n";

// ==========================================
// TEST 10: Canonical & Open Graph Metadata
// ==========================================
assert(str_contains($catRes->getContent(), 'rel="canonical"'), "Category page must have rel=canonical");
assert(str_contains($catRes->getContent(), 'property="og:title"'), "Category page must have og:title");
assert(str_contains($catRes->getContent(), 'property="og:description"'), "Category page must have og:description");
assert(str_contains($catRes->getContent(), 'property="og:url"'), "Category page must have og:url");
echo "Test 10: Canonical & Open Graph Metadata - PASSED\n";

echo "\nALL 10 WEBSITE CONTENT, LOCAL SEO & CMS TESTS PASSED SUCCESSFULLY!\n";
