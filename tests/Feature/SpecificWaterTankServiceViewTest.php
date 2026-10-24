<?php
require_once __DIR__ . '/../../public/index.php';

use App\Core\Request;
use App\Core\Response;
use App\Controllers\ServiceController;
use App\Models\Service;
use App\Models\ServiceVariant;

echo "\n=== RUNNING SPECIFIC WATER TANK SERVICE VIEW TEST SUITE ===\n\n";

$passCount = 0;
$failCount = 0;

function assertTrue($condition, $message) {
    global $passCount, $failCount;
    if ($condition) {
        echo "  [PASS] $message\n";
        $passCount++;
    } else {
        echo "  [FAIL] $message\n";
        $failCount++;
    }
}

// 1. Test ServiceController::serviceClean('water-tank-cleaning')
echo "[TEST 1] Testing /water-tank-cleaning resolves to specific service detail page...\n";
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/water-tank-cleaning';
$req = new Request();
$controller = new ServiceController();

$resp = $controller->serviceClean($req, 'water-tank-cleaning');
$content = $resp->getContent();

$svcDb = Service::findBySlug('water-tank-cleaning');
assertTrue($resp->getStatusCode() === 200, "Response status is 200 OK");
assertTrue(strpos($content, $svcDb['name']) !== false, "Page contains service name: '{$svcDb['name']}'");
assertTrue(strpos($content, 'Select Service Options') !== false || strpos($content, 'variant-pill-grid') !== false, "Page contains variant option/size selector");
assertTrue(strpos($content, '500 Litres') !== false, "Page contains '500 Litres' capacity variant");
assertTrue(strpos($content, '1000 Litres') !== false, "Page contains '1000 Litres' capacity variant");
assertTrue(strpos($content, 'Starting Price') !== false, "Page contains Starting Price highlight box");
assertTrue(strpos($content, 'Checklist') !== false || strpos($content, 'What is Included') !== false, "Page contains checklist items");

// 2. Test aliases resolve to water-tank-cleaning
echo "\n[TEST 2] Testing aliases 'tank-services', 'tank', 'tank-cleaning' in resolveService...\n";
$reflector = new ReflectionClass(ServiceController::class);
$resolveServiceMethod = $reflector->getMethod('resolveService');
$resolveServiceMethod->setAccessible(true);

$svcAlias1 = $resolveServiceMethod->invoke($controller, 'tank-services');
assertTrue(!empty($svcAlias1) && $svcAlias1['slug'] === 'water-tank-cleaning', "Alias 'tank-services' resolves to 'water-tank-cleaning'");

$svcAlias2 = $resolveServiceMethod->invoke($controller, 'tank-cleaning');
assertTrue(!empty($svcAlias2) && $svcAlias2['slug'] === 'water-tank-cleaning', "Alias 'tank-cleaning' resolves to 'water-tank-cleaning'");

$svcAlias3 = $resolveServiceMethod->invoke($controller, 'water-tank');
assertTrue(!empty($svcAlias3) && $svcAlias3['slug'] === 'water-tank-cleaning', "Alias 'water-tank' resolves to 'water-tank-cleaning'");

// 3. Test city-based URL /water-tank-cleaning-in-kashipur
echo "\n[TEST 3] Testing /water-tank-cleaning-in-kashipur resolves to service detail...\n";
$respCity = $controller->serviceInCity($req, 'water-tank-cleaning', 'kashipur');
$contentCity = $respCity->getContent();

assertTrue($respCity->getStatusCode() === 200, "City service response status is 200 OK");
assertTrue(strpos($contentCity, 'Kashipur') !== false, "Title / heading specifies Kashipur");
assertTrue(strpos($contentCity, '299') !== false, "Pricing reflects ₹299 starting price");

// 4. Test routes in routes.php for /services/{service}
echo "\n[TEST 4] Verifying /services/ route declarations and routes.php...\n";
$routesContent = file_get_contents(__DIR__ . '/../../config/routes.php');
assertTrue(strpos($routesContent, "Router::get('/services/{service}', 'ServiceController@serviceClean');") !== false, "Route /services/{service} exists");
assertTrue(strpos($routesContent, "Router::get('/services/{service}-in-{city}', 'ServiceController@serviceInCity');") !== false, "Route /services/{service}-in-{city} exists");
assertTrue(strpos($routesContent, "Router::get('/tank-services', function()") !== false, "Route /tank-services redirect exists");

// 5. Verifying Admin index view has View Live button
echo "\n[TEST 5] Verifying Admin index view has direct View Live link...\n";
$adminIndexView = file_get_contents(__DIR__ . '/../../app/views/admin/services/index.php');
assertTrue(strpos($adminIndexView, 'title="View Live Service Page"') !== false, "Admin view contains 'View Live Service Page' button");
assertTrue(strpos($adminIndexView, 'title="View live service page"') !== false, "Admin view contains clickable slug link with external icon");

echo "\n=========================================\n";
echo "TEST RESULTS: $passCount PASSED, $failCount FAILED\n";
echo "=========================================\n";

if ($failCount > 0) {
    exit(1);
}
exit(0);
