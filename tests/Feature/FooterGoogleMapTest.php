<?php

require_once __DIR__ . '/../bootstrap.php';

echo "=== RUNNING FOOTER GOOGLE MAP EMBED FEATURE TEST ===\n\n";

// Test 1: Footer partial renders with Google Map iframe
echo "[RUN] Test 1: Footer partial renders Google Maps iframe\n";
ob_start();
require ROOT_PATH . '/app/views/partials/footer.php';
$footerHtml = ob_get_clean();

assert(strpos($footerHtml, 'google.com/maps/embed') !== false, 'Google Maps embed URL missing from footer');
assert(strpos($footerHtml, 'footer-map-frame') !== false, 'footer-map-frame class missing from footer');
assert(strpos($footerHtml, '0x390a496b4e5d108d%3A0xf6d218115857576e') !== false, 'Refixel official Google Place ID missing from footer embed');
assert(strpos($footerHtml, 'cid=17785210960533280622') !== false, 'Direct Google Maps CID link missing from footer');

echo "[PASS] Test 1: Footer partial contains official Refixel Google Map embed and directions link.\n\n";

// Test 2: Contact page renders with Google Map iframe
echo "[RUN] Test 2: Contact view renders Google Maps iframe\n";
ob_start();
// Setup minimal dummy data for contact view if needed
$_SERVER['REQUEST_URI'] = '/contact';
require ROOT_PATH . '/app/views/customer/contact.php';
$contactHtml = ob_get_clean();

assert(strpos($contactHtml, 'google.com/maps/embed') !== false, 'Google Maps embed URL missing from contact page');
assert(strpos($contactHtml, '0x390a496b4e5d108d%3A0xf6d218115857576e') !== false, 'Refixel official Google Place ID missing from contact embed');

echo "[PASS] Test 2: Contact view contains official Refixel Google Map embed.\n\n";

echo "🎉 ALL FOOTER GOOGLE MAP TESTS PASSED PERFECTLY!\n";
