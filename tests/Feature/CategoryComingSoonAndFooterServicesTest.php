<?php
declare(strict_types=1);

require_once __DIR__ . '/../../public/index.php';

use App\Core\Database;
use App\Core\Request;
use App\Core\Csrf;
use App\Controllers\Admin\ServiceController as AdminServiceController;
use App\Controllers\ServiceController as CustomerServiceController;

echo "=== RUNNING CATEGORY COMING SOON, CATALOG & FOOTER FEATURE TEST ===\n\n";

// 1. Verify schema on categories table
$cols = Database::fetchAll("SHOW COLUMNS FROM categories LIKE 'is_coming_soon'");
assert(!empty($cols), "Column 'is_coming_soon' must exist on categories table");
echo "[PASS] Test 1: 'is_coming_soon' column confirmed on categories table.\n";

// 2. Test toggling Category Coming Soon
$firstCat = Database::fetchOne("SELECT id, name, is_coming_soon FROM categories ORDER BY id ASC LIMIT 1");
assert(!empty($firstCat), "At least one category must exist");
$catId = (int)$firstCat['id'];
$origStatus = (int)($firstCat['is_coming_soon'] ?? 0);

$adminCtrl = new AdminServiceController();

// Toggle ON
$toggleReq1 = new Request([], ['_csrf' => Csrf::token()], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/admin/services/categories/{$catId}/toggle-coming-soon"]);
$toggleResp1 = $adminCtrl->toggleCategoryComingSoon($toggleReq1, (string)$catId);
assert($toggleResp1->getStatusCode() === 302, "Expected redirect after toggle");

$updatedCat = Database::fetchOne("SELECT is_coming_soon FROM categories WHERE id = :id", ['id' => $catId]);
assert((int)$updatedCat['is_coming_soon'] === ($origStatus ? 0 : 1), "Category is_coming_soon should be inverted");
echo "[PASS] Test 2: Category toggle Coming Soon successfully inverted status.\n";

// Toggle BACK to original
$toggleReq2 = new Request([], ['_csrf' => Csrf::token()], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => "/admin/services/categories/{$catId}/toggle-coming-soon"]);
$adminCtrl->toggleCategoryComingSoon($toggleReq2, (string)$catId);
$revertedCat = Database::fetchOne("SELECT is_coming_soon FROM categories WHERE id = :id", ['id' => $catId]);
assert((int)$revertedCat['is_coming_soon'] === $origStatus, "Category is_coming_soon restored to original");
echo "[PASS] Test 3: Category toggle Coming Soon restored original status.\n";

// 3. Test Admin Categories View renders Coming Soon elements
$catIndexReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/admin/services/categories']);
$catIndexResp = $adminCtrl->categories($catIndexReq);
assert($catIndexResp->getStatusCode() === 200, "Expected 200 OK from /admin/services/categories");
$catHtml = $catIndexResp->getContent();

assert(str_contains($catHtml, 'Availability Status'), "Admin categories view should include 'Availability Status' header");
assert(str_contains($catHtml, 'toggle-coming-soon'), "Admin categories view should include toggle-coming-soon forms");
assert(str_contains($catHtml, 'new_cat_is_coming_soon'), "Admin categories view should have new_cat_is_coming_soon in Add modal");
assert(str_contains($catHtml, 'edit_cat_is_coming_soon'), "Admin categories view should have edit_cat_is_coming_soon in Edit modal");
echo "[PASS] Test 4: Admin categories page renders Coming Soon status, toggle buttons, and modal checkboxes.\n";

// 4. Test Customer AC service page has packages
$custCtrl = new CustomerServiceController();
$acReq = new Request([], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/ac-services-in-kashipur']);
$acResp = $custCtrl->servicesInCity($acReq, 'kashipur');
assert($acResp->getStatusCode() === 200, "Expected 200 OK from /ac-services-in-kashipur");
$acHtml = $acResp->getContent();

assert(!str_contains($acHtml, 'No packages available for this category yet'), "AC service page must have active packages");
assert(str_contains($acHtml, 'AC High-Pressure Jet Service') || str_contains($acHtml, 'ac-jet-service') || str_contains($acHtml, 'AC Service'), "AC service page should contain AC packages");
echo "[PASS] Test 5: AC service page loads with active packages and no empty state message.\n";

// 5. Test Footer contains coverage hubs and all services
$footerFile = file_get_contents(ROOT_PATH . '/app/views/partials/footer.php');
assert(str_contains($footerFile, 'AC High-Pressure Jet Wash'), "Footer should contain AC High-Pressure Jet Wash");
assert(str_contains($footerFile, 'AC Gas Leak'), "Footer should contain AC Gas Leak");
assert(str_contains($footerFile, 'Water Tank'), "Footer should contain Water Tank");
assert(str_contains($footerFile, 'Kashipur (244713)'), "Footer should contain Kashipur hub");
assert(str_contains($footerFile, 'Jaspur (244712)'), "Footer should contain Jaspur hub");
assert(str_contains($footerFile, 'Thakurdwara (244601)'), "Footer should contain Thakurdwara hub");
echo "[PASS] Test 6: Footer contains all service links and coverage hubs.\n";

// 6. Test Founders in About page and Footer
$aboutFile = file_get_contents(ROOT_PATH . '/app/views/customer/about.php');
assert(str_contains($aboutFile, 'Shanu Kumar'), "About page should contain Founder Shanu Kumar");
assert(str_contains($aboutFile, 'Manish Kumar'), "About page should contain Founder Manish Kumar");
assert(str_contains($aboutFile, 'Aakash Kumar'), "About page should contain Founder Aakash Kumar");

assert(str_contains($footerFile, 'Shanu Kumar'), "Footer should contain Founder Shanu Kumar");
assert(str_contains($footerFile, 'Manish Kumar'), "Footer should contain Founder Manish Kumar");
assert(str_contains($footerFile, 'Aakash Kumar'), "Footer should contain Founder Aakash Kumar");
echo "[PASS] Test 7: All 3 Founders (Shanu Kumar, Manish Kumar, Aakash Kumar) verified on About and Footer.\n";

// 7. Test SEO Keywords in Footer
assert(str_contains($footerFile, 'Popular Service &amp; Location Keywords:'), "Footer should contain SEO Keywords title");
assert(str_contains($footerFile, 'Split AC Jet Cleaning Kashipur'), "Footer should contain AC SEO keyword");
assert(str_contains($footerFile, 'Full Home Deep Cleaning Kashipur'), "Footer should contain Cleaning SEO keyword");
assert(str_contains($footerFile, 'Water Tank Cleaning Kashipur'), "Footer should contain Water Tank SEO keyword");
assert(str_contains($footerFile, 'Electrician in Kashipur'), "Footer should contain Electrician SEO keyword");
assert(str_contains($footerFile, 'Plumber in Kashipur'), "Footer should contain Plumber SEO keyword");
assert(str_contains($footerFile, 'House Painting Services Kashipur'), "Footer should contain Painting SEO keyword");
assert(str_contains($footerFile, 'Termite (Deemak) Treatment Kashipur'), "Footer should contain Termite SEO keyword");
assert(str_contains($footerFile, 'False Ceiling &amp; POP Contractor Kashipur'), "Footer should contain False ceiling SEO keyword");
assert(str_contains($footerFile, 'Washing Machine Repair Kashipur'), "Footer should contain Appliance repair SEO keyword");
assert(str_contains($footerFile, 'Verified Coverage Localities:'), "Footer should contain Coverage Localities");
echo "[PASS] Test 8: SEO Service & Location keyword clusters verified in Footer.\n";

echo "\n=== ALL TESTS PASSED SUCCESSFULLY! ===\n";
