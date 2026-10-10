<?php
declare(strict_types=1);

namespace Tests\Feature;

require __DIR__ . '/../bootstrap.php';

use App\Core\Request;
use App\Controllers\ServiceController;

echo "=== RUNNING SEO CLEAN URL & SERVICE CATALOGUE TESTS ===\n";

$ctrl = new ServiceController();

// Test 1: Category route with city
$req1 = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/cleaning-services-in-kashipur']);
$resp1 = $ctrl->categoryInCity($req1, 'cleaning', 'kashipur');
assert($resp1->getStatusCode() === 200, "Expected 200 for /cleaning-services-in-kashipur");
$html1 = $resp1->getContent();
assert(str_contains($html1, 'Cleaning'), "Expected Category name in content");
assert(str_contains($html1, 'application/ld+json'), "Expected JSON-LD schema markup");
assert(str_contains($html1, 'LocalBusiness'), "Expected LocalBusiness in JSON-LD");
assert(str_contains($html1, 'BreadcrumbList'), "Expected BreadcrumbList in JSON-LD");
echo "Test 1: Category in city (/cleaning-services-in-kashipur) - PASSED\n";

// Test 2a: Guest accessing service details is redirected to /login
unset($_SESSION['user']);
$reqGuest = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/full-home-cleaning-in-kashipur']);
$respGuest = $ctrl->serviceInCity($reqGuest, 'full-home-cleaning', 'kashipur');
assert($respGuest->getStatusCode() === 302, "Expected 302 redirect for guest accessing service detail");
assert(str_contains($respGuest->getHeader('Location') ?? '', '/login'), "Expected redirect to /login");
echo "Test 2a: Guest access requires login redirect (/login?redirect=...) - PASSED\n";

// Authenticate session for service page tests
$_SESSION['user'] = [
    'id'                   => 1,
    'role'                 => 'customer',
    'name'                 => 'Test Customer',
    'email'                => 'customer@refixel.com',
    'must_change_password' => false,
];
$_SESSION['last_activity'] = time();

// Test 2b: Service detail route with city (Authenticated Customer in Kashipur)
$req2 = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/full-home-cleaning-in-kashipur']);
$resp2 = $ctrl->serviceInCity($req2, 'full-home-cleaning', 'kashipur');
assert($resp2->getStatusCode() === 200, "Expected 200 for /full-home-cleaning-in-kashipur");
$html2 = $resp2->getContent();
assert(str_contains($html2, 'Professional Full Home Cleaning'), "Expected H1 with Service name");
assert(str_contains($html2, "What's Included"), "Expected What's Included checklist");
assert(str_contains($html2, "What's Excluded"), "Expected What's Excluded checklist");
assert(str_contains($html2, 'Transformation Showcase'), "Expected Before/After showcase");
assert(str_contains($html2, 'Verified Customer Reviews'), "Expected reviews section");
assert(str_contains($html2, 'AggregateRating'), "Expected AggregateRating in JSON-LD");
echo "Test 2b: Authenticated Service in city (/full-home-cleaning-in-kashipur) - PASSED\n";

// Test 2c: Non-allowed city (gurugram) redirects 301 to kashipur
$reqDisallowed = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/full-home-cleaning-in-gurugram']);
$respDisallowed = $ctrl->serviceInCity($reqDisallowed, 'full-home-cleaning', 'gurugram');
assert($respDisallowed->getStatusCode() === 301, "Expected 301 redirect for non-allowed city");
assert(str_contains($respDisallowed->getHeader('Location') ?? '', 'kashipur'), "Expected redirect to kashipur");
echo "Test 2c: Disallowed city 301 redirect to Kashipur - PASSED\n";

// Test 3: Cross-slug resolution (Service hitting category route pattern)
$req3 = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/full-home-cleaning-services-in-kashipur']);
$resp3 = $ctrl->categoryInCity($req3, 'full-home-cleaning-services', 'kashipur');
assert($resp3->getStatusCode() === 200, "Expected 200 for /full-home-cleaning-services-in-kashipur");
$html3 = $resp3->getContent();
assert(str_contains($html3, 'Professional Full Home Cleaning'), "Expected cross-resolution to service page");
echo "Test 3: Cross-slug resolution (/full-home-cleaning-services-in-kashipur) - PASSED\n";

// Test 4: Singular/Plural tolerance (e.g. plumber vs plumbers)
$req4 = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/plumber-services-in-kashipur']);
$resp4 = $ctrl->categoryInCity($req4, 'plumber', 'kashipur');
assert($resp4->getStatusCode() === 200, "Expected 200 for /plumber-services-in-kashipur");
assert(str_contains($resp4->getContent(), 'Plumbers'), "Expected Plumbers category matched");
echo "Test 4: Singular/Plural tolerance (/plumber-services-in-kashipur) - PASSED\n";

// Test 5: AC service slug tolerance (ac-services vs ac)
$req5 = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/ac-services-in-kashipur']);
$resp5 = $ctrl->categoryInCity($req5, 'ac-services', 'kashipur');
assert($resp5->getStatusCode() === 200, "Expected 200 for /ac-services-in-kashipur");
assert(str_contains($resp5->getContent(), 'AC Service & Repair'), "Expected AC category matched");
echo "Test 5: AC slug tolerance (/ac-services-in-kashipur) - PASSED\n";

// Test 6: Newly seeded Electrician in Kashipur
$req6 = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/electrician-services-in-kashipur']);
$resp6 = $ctrl->categoryInCity($req6, 'electrician', 'kashipur');
assert($resp6->getStatusCode() === 200, "Expected 200 for /electrician-services-in-kashipur");
assert(str_contains($resp6->getContent(), 'Electrician'), "Expected Electrician category matched");
echo "Test 6: Electrician in Kashipur (/electrician-services-in-kashipur) - PASSED\n";

// Test 7: Non-existent category or service returns 404
$req7 = new Request([], [], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/nonexistent-services-in-kashipur']);
$resp7 = $ctrl->categoryInCity($req7, 'nonexistent', 'kashipur');
assert($resp7->getStatusCode() === 404, "Expected 404 for non-existent service/category");
echo "Test 7: 404 for invalid category/service - PASSED\n";

echo "=== ALL SEO CLEAN URL & SERVICE CATALOGUE TESTS PASSED! ===\n";
