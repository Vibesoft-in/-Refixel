<?php
declare(strict_types=1);

namespace Tests\Feature;

require_once __DIR__ . '/../bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Theme;
use App\Models\Setting;
use App\Models\User;
use App\Controllers\SuperAdmin\ThemeController;

echo "=== RUNNING SUPER ADMIN WHITE-LABEL THEME CUSTOMIZER TESTS ===\n\n";

// Ensure a super admin user exists
$superAdmin = Database::fetchOne("SELECT * FROM users WHERE role = 'super_admin' LIMIT 1");
if (!$superAdmin) {
    Database::query(
        "INSERT INTO users (name, email, password_hash, role, is_active, created_at) 
         VALUES ('Super Admin', 'superadmin@refixel.com', :pwd, 'super_admin', 1, NOW())",
        ['pwd' => password_hash('SuperSecret@123', PASSWORD_BCRYPT)]
    );
    $superAdmin = Database::fetchOne("SELECT * FROM users WHERE role = 'super_admin' LIMIT 1");
}

// -------------------------------------------------------------
// TEST 1: Theme Core Class Defaults & Presets
// -------------------------------------------------------------
echo "[RUN] Test 1: Theme Core Defaults & Presets Structure\n";
assert(count(Theme::PRESETS) >= 6, "Expected at least 6 presets in Theme::PRESETS");
assert(isset(Theme::PRESETS['refixel_signature']), "Missing refixel_signature preset");
assert(isset(Theme::PRESETS['corporate_blue']), "Missing corporate_blue preset");
assert(isset(Theme::PRESETS['emerald_clean']), "Missing emerald_clean preset");
assert(count(Theme::FONTS) >= 8, "Expected at least 8 Google Fonts in Theme::FONTS");

$renderedHead = Theme::renderHead();
assert(str_contains($renderedHead, '--primary-color'), "Theme::renderHead should output --primary-color");
assert(str_contains($renderedHead, '--secondary-color'), "Theme::renderHead should output --secondary-color");
assert(str_contains($renderedHead, 'fonts.googleapis.com'), "Theme::renderHead should link to Google Fonts");
echo "[PASS] Test 1: Theme Core Defaults & Presets verified successfully.\n\n";

// -------------------------------------------------------------
// TEST 2: ThemeController Index Render
// -------------------------------------------------------------
echo "[RUN] Test 2: ThemeController index view renders with all preset data\n";
Auth::login($superAdmin);
$controller = new ThemeController();
$req = new Request([], [], [], [], ['REQUEST_METHOD' => 'GET', 'REQUEST_URI' => '/superadmin/theme']);
$resp = $controller->index($req);

assert($resp instanceof Response, "Expected Response from ThemeController::index");
assert($resp->getStatusCode() === 200, "Expected status code 200, got: " . $resp->getStatusCode());
$body = $resp->getBody();
assert(str_contains($body, 'Theme & White-Label Customizer'), "Missing theme customizer heading in view");
assert(str_contains($body, '1-Click Client Presets'), "Missing presets section in view");
assert(str_contains($body, 'Corporate Royal'), "Missing Corporate preset in view");
assert(str_contains($body, 'Real-time Client Preview'), "Missing live preview simulator in view");
echo "[PASS] Test 2: ThemeController index view rendered successfully with 200 OK.\n\n";

// -------------------------------------------------------------
// TEST 3: Switching to Preset (Corporate Blue)
// -------------------------------------------------------------
echo "[RUN] Test 3: Applying Preset (Corporate Blue) via ThemeController::save\n";
$saveReq = new Request([
    'theme_preset'           => 'corporate_blue',
    'apply_preset_defaults'  => '1',
    'business_name'          => 'Apex Corporate Facilities',
    'hero_tagline'           => 'Enterprise cleaning & maintenance solutions',
], [], [], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/superadmin/theme/save']);

$saveResp = $controller->save($saveReq);
assert($saveResp->getStatusCode() === 302, "Expected redirect 302 from save, got: " . $saveResp->getStatusCode());

// Verify settings updated in Database
assert(Theme::getPreset() === 'corporate_blue', "Theme preset did not update to corporate_blue");
assert(Theme::getPrimaryColor() === '#2563eb', "Primary color did not update to #2563eb, got: " . Theme::getPrimaryColor());
assert(Theme::getFontSlug() === 'inter', "Font slug did not update to inter, got: " . Theme::getFontSlug());
assert(Theme::getBrandName() === 'Apex Corporate Facilities', "Brand name did not update");

// Verify Theme::renderHead reflects the new Corporate Blue theme
$corpHead = Theme::renderHead();
assert(str_contains($corpHead, '#2563eb'), "Theme::renderHead does not contain #2563eb");
assert(str_contains($corpHead, 'Inter'), "Theme::renderHead does not link to Inter font");
echo "[PASS] Test 3: Applied Corporate Blue preset. Website dynamically rebranded to Apex Corporate Facilities.\n\n";

// -------------------------------------------------------------
// TEST 4: Custom Color & Font Tweaks
// -------------------------------------------------------------
echo "[RUN] Test 4: Custom manual hex colors & Poppins font\n";
$customReq = new Request([
    'theme_preset'        => 'custom',
    'theme_primary_color' => '#059669',
    'theme_primary_hover' => '#047857',
    'theme_secondary_color'=> '#064e3b',
    'theme_accent_color'  => '#34d399',
    'theme_font_slug'     => 'poppins',
    'theme_border_radius' => '16px',
    'business_name'       => 'EcoClean Doorstep Services',
    'hero_tagline'        => '100% Eco-friendly home cleaning',
], [], [], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/superadmin/theme/save']);

$customResp = $controller->save($customReq);
assert($customResp->getStatusCode() === 302, "Expected redirect 302");
assert(Theme::getPrimaryColor() === '#059669', "Primary color did not update to #059669");
assert(Theme::getFontSlug() === 'poppins', "Font slug did not update to poppins");
assert(Theme::getBorderRadius() === '16px', "Border radius did not update to 16px");

$customHead = Theme::renderHead();
assert(str_contains($customHead, '#059669'), "Custom head does not contain #059669");
assert(str_contains($customHead, 'Poppins'), "Custom head does not contain Poppins font");
echo "[PASS] Test 4: Custom palette (#059669) & Poppins font verified.\n\n";

// -------------------------------------------------------------
// TEST 5: Reset Theme to Refixel Default
// -------------------------------------------------------------
echo "[RUN] Test 5: Resetting Theme to Refixel Default\n";
$resetReq = new Request([], [], [], [], ['REQUEST_METHOD' => 'POST', 'REQUEST_URI' => '/superadmin/theme/reset']);
$resetResp = $controller->reset($resetReq);
assert($resetResp->getStatusCode() === 302, "Expected redirect 302");

// Verify default values restored
assert(Theme::getPreset() === 'refixel_signature', "Expected preset refixel_signature");
assert(Theme::getPrimaryColor() === '#f25b29', "Expected primary color #f25b29, got: " . Theme::getPrimaryColor());
assert(Theme::getFontSlug() === 'outfit', "Expected font slug outfit, got: " . Theme::getFontSlug());
assert(Theme::getSecondaryColor() === '#0a1c33', "Expected secondary color #0a1c33");

$defaultHead = Theme::renderHead();
assert(str_contains($defaultHead, '#f25b29'), "Default head does not contain #f25b29");
assert(str_contains($defaultHead, 'Outfit'), "Default head does not link to Outfit font");

// Restore brand name
Setting::set('business_name', 'REFIXEL');
Setting::set('company_name', 'REFIXEL Home Services');
echo "[PASS] Test 5: Theme successfully reset to REFIXEL signature brand identity.\n\n";

echo "🎉 ALL SUPER ADMIN WHITE-LABEL THEME CUSTOMIZER TESTS PASSED PERFECTLY!\n";
