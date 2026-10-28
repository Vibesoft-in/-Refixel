<?php
declare(strict_types=1);

use App\Core\Router;

// ==========================================
// PUBLIC CUSTOMER ROUTES
// ==========================================
Router::get('/', 'HomeController@index');
Router::get('/index.php', 'HomeController@index'); // Legacy alias

Router::get('/about', 'PageController@about');
Router::get('/about-us.php', function() {
    return (new \App\Core\Response())->redirect(\App\Core\View::url('/about'), 301);
});

Router::get('/faq', 'PageController@faq');
Router::get('/faqs.php', function() {
    return (new \App\Core\Response())->redirect(\App\Core\View::url('/faq'), 301);
});

Router::get('/gallery', 'PageController@gallery');

Router::get('/contact', 'PageController@contact');
Router::get('/contact-us.php', function() {
    return (new \App\Core\Response())->redirect(\App\Core\View::url('/contact'), 301);
});
Router::post('/contact', 'PageController@submitContact', ['RateLimit:5,60', 'VerifyCsrf']);

Router::get('/terms', 'PageController@terms');
Router::get('/terms-and-conditions.php', function() {
    return (new \App\Core\Response())->redirect(\App\Core\View::url('/terms'), 301);
});

Router::get('/privacy', 'PageController@privacy');
Router::get('/privacy-policy.php', function() {
    return (new \App\Core\Response())->redirect(\App\Core\View::url('/privacy'), 301);
});

Router::get('/refund', 'PageController@refund');
Router::get('/refund-policy.php', function() {
    return (new \App\Core\Response())->redirect(\App\Core\View::url('/refund'), 301);
});

Router::get('/blog', 'PageController@blog');
Router::get('/blog.php', function() {
    return (new \App\Core\Response())->redirect(\App\Core\View::url('/blog'), 301);
});

Router::get('/partner', 'PageController@partner');
Router::post('/partner', 'PageController@submitPartner', ['RateLimit:5,60', 'VerifyCsrf']);

Router::get('/services', 'ServiceController@index');
Router::get('/services-in-{city}', 'ServiceController@servicesInCity');

Router::get('/sitemap.xml', 'PageController@sitemap');
Router::get('/robots.txt', 'PageController@robots');


// 301 Redirect for plumber URL consistency (/plumbers-services -> /plumber-services)
Router::get('/plumbers-services', function() {
    return (new \App\Core\Response())->redirect(\App\Core\View::url('/plumber-services'), 301);
});
Router::get('/plumbers-services-in-{city}', function(\App\Core\Request $request, string $city) {
    return (new \App\Core\Response())->redirect(\App\Core\View::url("/plumber-services-in-{$city}"), 301);
});

// 301 Redirect for tank cleaning queries directly to specific water tank service
Router::get('/tank-cleaning', function() {
    return (new \App\Core\Response())->redirect(\App\Core\View::url('/water-tank-cleaning'), 301);
});
Router::get('/tank-cleaning-in-{city}', function(\App\Core\Request $request, string $city) {
    return (new \App\Core\Response())->redirect(\App\Core\View::url("/water-tank-cleaning-in-{$city}"), 301);
});
Router::get('/tank-services', function() {
    return (new \App\Core\Response())->redirect(\App\Core\View::url('/water-tank-cleaning'), 301);
});
Router::get('/tank-services-in-{city}', function(\App\Core\Request $request, string $city) {
    return (new \App\Core\Response())->redirect(\App\Core\View::url("/water-tank-cleaning-in-{$city}"), 301);
});
Router::get('/tank-cleaning-services', function() {
    return (new \App\Core\Response())->redirect(\App\Core\View::url('/water-tank-cleaning'), 301);
});
Router::get('/tank-cleaning-services-in-{city}', function(\App\Core\Request $request, string $city) {
    return (new \App\Core\Response())->redirect(\App\Core\View::url("/water-tank-cleaning-in-{$city}"), 301);
});

// Clean URLs for categories (e.g. /cleaning-services, /painting-services)
Router::get('/{category}-services', 'ServiceController@categoryClean');

// SEO URLs for categories and services with city (backward-compatible)
Router::get('/{category}-services-in-{city}', 'ServiceController@categoryInCity');
Router::get('/{service}-in-{city}', 'ServiceController@serviceInCity');

// Booking Flow & Cart
Router::get('/book', 'BookingController@showForm');
Router::post('/book', 'BookingController@submit', ['RateLimit:60,60', 'VerifyCsrf']);
Router::post('/book/verify-payment', 'BookingController@verifyPayment', ['RateLimit:30,60', 'VerifyCsrf']);
Router::get('/book-success', 'BookingController@success');
Router::post('/webhook/razorpay', 'WebhookController@razorpay'); // Webhook exempt from CSRF and RateLimit, secured by HMAC

Router::get('/cart', 'BookingController@cart');
Router::get('/cart.php', function() {
    return (new \App\Core\Response())->redirect(\App\Core\View::url('/cart'), 301);
});
Router::get('/api/cart', 'BookingController@apiCart');
Router::get('/api/cart/addons', 'BookingController@apiCartAddons');
Router::post('/api/cart/add', 'BookingController@addToCart', ['RateLimit:60,60', 'VerifyCsrf']);
Router::post('/api/cart/update', 'BookingController@updateCart', ['RateLimit:60,60', 'VerifyCsrf']);
Router::post('/api/cart/remove', 'BookingController@removeFromCart', ['RateLimit:60,60', 'VerifyCsrf']);
Router::post('/api/cart/clear', 'BookingController@clearCart', ['RateLimit:60,60', 'VerifyCsrf']);
Router::get('/api/services', 'ServiceController@apiSearch');
Router::post('/api/customer/save-address', 'AccountController@apiSaveAddress', ['RequireLogin', 'VerifyCsrf']);
Router::get('/api/customer/profile', 'AccountController@apiGetProfile');
Router::get('/api/customer/addresses', 'AccountController@apiGetSavedAddresses');
Router::post('/api/customer/delete-address', 'AccountController@apiDeleteAddress', ['RequireLogin', 'VerifyCsrf']);

// Legacy ajax endpoints compatibility
Router::post('/cartajax/ajax_cart_add.php', 'BookingController@addToCart', ['RateLimit:60,60', 'VerifyCsrf']);
Router::post('/cartajax/ajax_cart_update.php', 'BookingController@updateCart', ['RateLimit:60,60', 'VerifyCsrf']);


// ==========================================
// UNIFIED AUTHENTICATION (ALL ROLES)
// ==========================================
Router::get('/login', 'AuthController@showLogin');
Router::post('/login', 'AuthController@login', ['RateLimit:5,900', 'VerifyCsrf']);

// Dedicated Admin & SuperAdmin Login Aliases
Router::get('/admin/login', 'AuthController@showLogin');
Router::post('/admin/login', 'AuthController@login', ['RateLimit:5,900', 'VerifyCsrf']);
Router::get('/superadmin/login', 'AuthController@showLogin');
Router::post('/superadmin/login', 'AuthController@login', ['RateLimit:5,900', 'VerifyCsrf']);

Router::get('/signup', 'AuthController@showSignup');
Router::post('/signup', 'AuthController@signup', ['RateLimit:5,300', 'VerifyCsrf']);

Router::get('/forgot-password', 'AuthController@showForgotPassword');
Router::post('/forgot-password', 'AuthController@forgotPassword', ['RateLimit:5,300', 'VerifyCsrf']);

Router::get('/reset-password', 'AuthController@showResetPassword');
Router::post('/reset-password', 'AuthController@resetPassword', ['RateLimit:5,300', 'VerifyCsrf']);

Router::get('/change-password', 'AuthController@showChangePassword', ['RequireLogin']);
Router::post('/change-password', 'AuthController@changePassword', ['RequireLogin', 'VerifyCsrf']);

// Two-Factor Authentication (2FA - TOTP)
Router::get('/login/2fa', 'AuthController@show2fa');
Router::post('/login/2fa', 'AuthController@verify2fa', ['RateLimit:5,900', 'VerifyCsrf']);
Router::get('/2fa/setup', 'AuthController@show2faSetup', ['RequireLogin']);
Router::post('/2fa/setup', 'AuthController@confirm2faSetup', ['RequireLogin', 'VerifyCsrf']);

Router::get('/logout', 'AuthController@logout');
Router::get('/logout.php', function() {
    return (new \App\Core\Response())->redirect(\App\Core\View::url('/logout'), 301);
});

// ==========================================
// CUSTOMER PORTAL (/account)
// ==========================================
// Legacy alias redirect to customer bookings
Router::get('/my-booking.php', function() {
    return (new \App\Core\Response())->redirect(\App\Core\View::url('/account/bookings'), 301);
});

Router::group(['middleware' => ['RequireRole:customer']], function () {
    Router::get('/account', 'AccountController@index');
    Router::get('/account/bookings', 'AccountController@bookings');
    Router::get('/account/bookings/{id}', 'AccountController@bookingDetail');
    Router::post('/account/bookings/{id}/cancel', 'AccountController@cancelBooking', ['VerifyCsrf']);
    Router::post('/account/bookings/{id}/reschedule', 'AccountController@rescheduleBooking', ['VerifyCsrf']);
    Router::post('/account/bookings/{id}/refund-request', 'AccountController@requestRefund', ['VerifyCsrf']);
    Router::post('/account/bookings/{id}/review', 'AccountController@submitReview', ['VerifyCsrf']);
    Router::get('/account/invoices', 'AccountController@invoices');
    Router::get('/account/invoices/{id}', 'AccountController@invoiceDetail');
    Router::get('/account/profile', 'AccountController@profile');
    Router::post('/account/profile', 'AccountController@updateProfile', ['VerifyCsrf']);
    Router::get('/account/privacy', 'AccountController@privacy');
    Router::get('/account/privacy/export', 'AccountController@exportData');
    Router::post('/account/privacy/delete-request', 'AccountController@requestDataDeletion', ['VerifyCsrf']);
});

// ==========================================
// SUPER ADMIN PORTAL (/superadmin)
// ==========================================
Router::group(['middleware' => ['RequireRole:super_admin']], function () {
    Router::get('/superadmin', 'SuperAdmin\\DashboardController@index');
    Router::get('/superadmin/dashboard', 'SuperAdmin\\DashboardController@index');

    // Bulk & Single Deletion - Enquiries
    Router::get('/superadmin/enquiries', 'SuperAdmin\\DashboardController@enquiries');
    Router::post('/superadmin/enquiries/delete-all', 'SuperAdmin\\DashboardController@deleteAllEnquiries', ['VerifyCsrf']);
    Router::post('/superadmin/enquiries/{id}/delete', 'SuperAdmin\\DashboardController@deleteEnquiry', ['VerifyCsrf']);

    // Bulk & Single Deletion - Technicians (Staff)
    Router::get('/superadmin/staff', 'SuperAdmin\\DashboardController@staff');
    Router::post('/superadmin/staff/delete-all', 'SuperAdmin\\DashboardController@deleteAllStaff', ['VerifyCsrf']);
    Router::post('/superadmin/staff/{id}/delete', 'SuperAdmin\\DashboardController@deleteStaff', ['VerifyCsrf']);
    Router::post('/superadmin/staff/{id}/approve', 'SuperAdmin\\DashboardController@approveStaff', ['VerifyCsrf']);

    // Partner Applications Review (Super Admin)
    Router::get('/superadmin/partner-applications', 'SuperAdmin\\DashboardController@partnerApplications');
    Router::post('/superadmin/partner-applications/{id}/approve', 'SuperAdmin\\DashboardController@approvePartnerApplication', ['VerifyCsrf']);
    Router::post('/superadmin/partner-applications/{id}/reject', 'SuperAdmin\\DashboardController@rejectPartnerApplication', ['VerifyCsrf']);

    // Bulk & Single Deletion - Customers
    Router::get('/superadmin/customers', 'SuperAdmin\\DashboardController@customers');
    Router::post('/superadmin/customers/delete-all', 'SuperAdmin\\DashboardController@deleteAllCustomers', ['VerifyCsrf']);
    Router::post('/superadmin/customers/{id}/delete', 'SuperAdmin\\DashboardController@deleteCustomer', ['VerifyCsrf']);

    // Bulk & Single Deletion - Bookings & Jobs
    Router::post('/superadmin/bookings/delete-all', 'SuperAdmin\\DashboardController@deleteAllBookings', ['VerifyCsrf']);
    Router::post('/superadmin/bookings/{id}/delete', 'SuperAdmin\\DashboardController@deleteBooking', ['VerifyCsrf']);

    // Bulk Deletion - Dispatch Calendar
    Router::post('/superadmin/calendar/delete-all', 'SuperAdmin\\DashboardController@deleteAllCalendar', ['VerifyCsrf']);

    // Operations Admin Governance
    Router::get('/superadmin/admins', 'SuperAdmin\\DashboardController@admins');
    Router::post('/superadmin/admins', 'SuperAdmin\\DashboardController@storeAdmin', ['VerifyCsrf']);
    Router::post('/superadmin/admins/{id}/reset-password', 'SuperAdmin\\DashboardController@updateAdminPassword', ['VerifyCsrf']);
    Router::post('/superadmin/admins/{id}/reset-2fa', 'SuperAdmin\\DashboardController@resetAdmin2fa', ['VerifyCsrf']);
    Router::post('/superadmin/admins/delete-all', 'SuperAdmin\\DashboardController@deleteAllAdmins', ['VerifyCsrf']);
    Router::post('/superadmin/admins/{id}/delete', 'SuperAdmin\\DashboardController@deleteAdmin', ['VerifyCsrf']);
    Router::post('/superadmin/users/{id}/reset-2fa', 'SuperAdmin\\DashboardController@resetUser2fa', ['VerifyCsrf']);

    // Payment Gateways & API Credentials (Exclusively Super Admin)
    Router::get('/superadmin/gateways', 'SuperAdmin\\GatewayController@index');
    Router::post('/superadmin/gateways/advance-policy', 'SuperAdmin\\GatewayController@updateAdvancePolicy', ['VerifyCsrf']);
    Router::post('/superadmin/gateways/{id}/update', 'SuperAdmin\\GatewayController@update', ['VerifyCsrf']);
    Router::post('/superadmin/gateways/{id}/toggle', 'SuperAdmin\\GatewayController@toggle', ['VerifyCsrf']);
    Router::post('/superadmin/gateways/{id}/test', 'SuperAdmin\\GatewayController@test', ['VerifyCsrf']);

    // Commissions & Profit Splits (Exclusively Super Admin)
    Router::get('/superadmin/commissions', 'SuperAdmin\\CommissionController@index');
    Router::post('/superadmin/commissions/global', 'SuperAdmin\\CommissionController@saveGlobal', ['VerifyCsrf']);
    Router::post('/superadmin/commissions/rules', 'SuperAdmin\\CommissionController@storeRule', ['VerifyCsrf']);
    Router::post('/superadmin/commissions/rules/{id}/update', 'SuperAdmin\\CommissionController@updateRule', ['VerifyCsrf']);
    Router::post('/superadmin/commissions/rules/{id}/delete', 'SuperAdmin\\CommissionController@deleteRule', ['VerifyCsrf']);
    Router::post('/superadmin/commissions/settle/{id}', 'SuperAdmin\\CommissionController@settleEarning', ['VerifyCsrf']);
    Router::get('/superadmin/commissions/simulate', 'SuperAdmin\\CommissionController@simulate');

    // Technician Payouts & Settlements (Super Admin Direct Access)
    Router::get('/superadmin/payouts', 'Admin\\PayoutController@index');
    Router::post('/superadmin/payouts/{id}/settle', 'Admin\\PayoutController@settle', ['VerifyCsrf']);
    Router::post('/superadmin/payouts/technicians/{id}/settle-all', 'Admin\\PayoutController@settleAll', ['VerifyCsrf']);

    // White-Label Branding & Theme Customizer (Exclusively Super Admin)
    Router::get('/superadmin/theme', 'SuperAdmin\\ThemeController@index');
    Router::post('/superadmin/theme/save', 'SuperAdmin\\ThemeController@save', ['VerifyCsrf']);
    Router::post('/superadmin/theme/reset', 'SuperAdmin\\ThemeController@reset', ['VerifyCsrf']);

    // Super Admin Profile & Security Controls
    Router::get('/superadmin/profile', 'SuperAdmin\\DashboardController@profile');
    Router::post('/superadmin/profile', 'SuperAdmin\\DashboardController@updateProfile', ['VerifyCsrf']);
    Router::post('/superadmin/profile/password', 'SuperAdmin\\DashboardController@updatePassword', ['VerifyCsrf']);
    Router::post('/superadmin/profile/disable-2fa', 'SuperAdmin\\DashboardController@disableMy2fa', ['VerifyCsrf']);
});

// ==========================================
// ADMIN PORTAL (/admin)
// ==========================================
Router::group(['middleware' => ['RequireRole:admin']], function () {
    Router::get('/admin', 'Admin\\DashboardController@index');
    Router::get('/admin/dashboard', 'Admin\\DashboardController@index');
    Router::post('/admin/api/toggle-busy-mode', 'Admin\\DashboardController@toggleBusyMode', ['VerifyCsrf']);
    Router::get('/admin/api/busy-mode-status', 'Admin\\DashboardController@getBusyModeStatus');

    // Enquiries
    Router::get('/admin/enquiries', 'Admin\\EnquiryController@index');
    Router::get('/admin/enquiries/{id}', 'Admin\\EnquiryController@show');
    Router::post('/admin/enquiries/{id}/update', 'Admin\\EnquiryController@update', ['VerifyCsrf']);

    // Bookings & Dispatch Calendar
    Router::get('/admin/bookings', 'Admin\\BookingController@index');
    Router::post('/admin/bookings/delete-all', 'Admin\\BookingController@deleteAllBookings', ['VerifyCsrf']);
    Router::get('/admin/bookings/{id}', 'Admin\\BookingController@show');
    Router::post('/admin/bookings/{id}/assign', 'Admin\\BookingController@assignStaff', ['VerifyCsrf']);
    Router::post('/admin/bookings/{id}/reschedule', 'Admin\\BookingController@reschedule', ['VerifyCsrf']);
    Router::post('/admin/bookings/{id}/status', 'Admin\\BookingController@updateStatus', ['VerifyCsrf']);
    Router::get('/admin/calendar', 'Admin\\BookingController@calendar');
    Router::post('/admin/calendar/delete-all', 'Admin\\BookingController@deleteAllCalendar', ['VerifyCsrf']);

    // Abandoned Checkouts & Payment Drop-offs
    Router::get('/admin/dropoffs', 'Admin\\DropoffController@index');
    Router::post('/admin/dropoffs/{id}/convert-cod', 'Admin\\DropoffController@convertToCod', ['VerifyCsrf']);
    Router::post('/admin/dropoffs/{id}/mark-recovered', 'Admin\\DropoffController@markRecovered', ['VerifyCsrf']);
    Router::post('/admin/dropoffs/{id}/delete', 'Admin\\DropoffController@delete', ['VerifyCsrf']);
    Router::post('/admin/dropoffs/delete-all', 'Admin\\DropoffController@deleteAll', ['VerifyCsrf']);

    // Staff
    Router::get('/admin/staff', 'Admin\\StaffController@index');
    Router::get('/admin/staff/create', 'Admin\\StaffController@create');
    Router::post('/admin/staff', 'Admin\\StaffController@store', ['VerifyCsrf']);
    Router::get('/admin/staff/{id}', 'Admin\\StaffController@show');
    Router::post('/admin/staff/{id}/approve', 'Admin\\StaffController@approve', ['VerifyCsrf']);
    Router::post('/admin/staff/{id}/toggle-status', 'Admin\\StaffController@toggleStatus', ['VerifyCsrf']);
    Router::post('/admin/staff/{id}/skills', 'Admin\\StaffController@updateSkills', ['VerifyCsrf']);

    // Partner Applications & Job Submissions
    Router::get('/admin/partner-applications', 'Admin\\PartnerApplicationController@index');
    Router::post('/admin/partner-applications/{id}/approve', 'Admin\\PartnerApplicationController@approve', ['VerifyCsrf']);
    Router::post('/admin/partner-applications/{id}/reject', 'Admin\\PartnerApplicationController@reject', ['VerifyCsrf']);
    Router::post('/admin/partner-applications/{id}/delete', 'Admin\\PartnerApplicationController@delete', ['VerifyCsrf']);

    // Services & Categories
    Router::get('/admin/services', 'Admin\\ServiceController@index');
    Router::post('/admin/services', 'Admin\\ServiceController@storeService', ['VerifyCsrf']);
    Router::post('/admin/services/{id}/update', 'Admin\\ServiceController@updateService', ['VerifyCsrf']);
    Router::post('/admin/services/{id}/delete', 'Admin\\ServiceController@deleteService', ['VerifyCsrf']);
    Router::post('/admin/services/{id}/toggle-status', 'Admin\\ServiceController@toggleServiceStatus', ['VerifyCsrf']);
    Router::post('/admin/services/{id}/toggle-addon', 'Admin\\ServiceController@toggleAddonStatus', ['VerifyCsrf']);
    Router::post('/admin/services/{id}/toggle-coming-soon', 'Admin\\ServiceController@toggleComingSoon', ['VerifyCsrf']);
    Router::get('/admin/services/categories', 'Admin\\ServiceController@categories');
    Router::post('/admin/services/categories', 'Admin\\ServiceController@storeCategory', ['VerifyCsrf']);
    Router::post('/admin/services/categories/{id}/update', 'Admin\\ServiceController@updateCategory', ['VerifyCsrf']);
    Router::post('/admin/services/categories/{id}/delete', 'Admin\\ServiceController@deleteCategory', ['VerifyCsrf']);
    Router::post('/admin/services/categories/{id}/toggle-status', 'Admin\\ServiceController@toggleCategoryStatus', ['VerifyCsrf']);

    // Service Variations & Options
    Router::get('/admin/services/{id}/variants', 'Admin\\ServiceController@variants');
    Router::get('/admin/services/{id}/variants-json', 'Admin\\ServiceController@variantsJson');
    Router::post('/admin/services/{id}/variants', 'Admin\\ServiceController@storeVariant', ['VerifyCsrf']);
    Router::post('/admin/services/variants/{id}/update', 'Admin\\ServiceController@updateVariant', ['VerifyCsrf']);
    Router::post('/admin/services/variants/{id}/quick-price', 'Admin\\ServiceController@quickPriceVariant', ['VerifyCsrf']);
    Router::post('/admin/services/{id}/add-quick-variant', 'Admin\\ServiceController@addQuickVariant', ['VerifyCsrf']);
    Router::post('/admin/services/variants/{id}/delete', 'Admin\\ServiceController@deleteVariant', ['VerifyCsrf']);
    Router::post('/admin/services/variants/{id}/toggle-status', 'Admin\\ServiceController@toggleVariantStatus', ['VerifyCsrf']);

    // Service Add-ons & Smart Pairing Management
    Router::get('/admin/services/addons', 'Admin\\ServiceAddonController@index');
    Router::post('/admin/services/addons', 'Admin\\ServiceAddonController@store', ['VerifyCsrf']);
    Router::post('/admin/services/addons/toggle-auto-engine', 'Admin\\ServiceAddonController@toggleAutoEngine', ['VerifyCsrf']);
    Router::post('/admin/services/addons/create-addon-service', 'Admin\\ServiceAddonController@createAddonService', ['VerifyCsrf']);
    Router::post('/admin/services/addons/rename', 'Admin\\ServiceAddonController@updateAddonName', ['VerifyCsrf']);
    Router::post('/admin/services/addons/{id}/update', 'Admin\\ServiceAddonController@update', ['VerifyCsrf']);
    Router::post('/admin/services/addons/{id}/delete', 'Admin\\ServiceAddonController@delete', ['VerifyCsrf']);
    Router::post('/admin/services/addons/{id}/toggle-status', 'Admin\\ServiceAddonController@toggleStatus', ['VerifyCsrf']);

    // Payment Gateways & Credentials
    Router::get('/admin/gateways', 'Admin\\GatewayController@index');
    Router::post('/admin/gateways/{id}/update', 'Admin\\GatewayController@update', ['VerifyCsrf']);
    Router::post('/admin/gateways/{id}/toggle', 'Admin\\GatewayController@toggle', ['VerifyCsrf']);
    Router::post('/admin/gateways/{id}/test', 'Admin\\GatewayController@test', ['VerifyCsrf']);

    // Commissions & Profit Splits
    Router::get('/admin/commissions', 'Admin\\CommissionController@index');
    Router::post('/admin/commissions/global', 'Admin\\CommissionController@saveGlobal', ['VerifyCsrf']);
    Router::post('/admin/commissions/rules', 'Admin\\CommissionController@storeRule', ['VerifyCsrf']);
    Router::post('/admin/commissions/rules/{id}/update', 'Admin\\CommissionController@updateRule', ['VerifyCsrf']);
    Router::post('/admin/commissions/rules/{id}/delete', 'Admin\\CommissionController@deleteRule', ['VerifyCsrf']);
    Router::post('/admin/commissions/settle/{id}', 'Admin\\CommissionController@settleEarning', ['VerifyCsrf']);
    Router::get('/admin/commissions/simulate', 'Admin\\CommissionController@simulate');

    // Technician Payouts & Settlements
    Router::get('/admin/payouts', 'Admin\\PayoutController@index');
    Router::post('/admin/payouts/{id}/settle', 'Admin\\PayoutController@settle', ['VerifyCsrf']);
    Router::post('/admin/payouts/technicians/{id}/settle-all', 'Admin\\PayoutController@settleAll', ['VerifyCsrf']);

    // Payments & Invoices
    Router::get('/admin/payments', 'Admin\\PaymentController@index');
    Router::post('/admin/payments', 'Admin\\PaymentController@recordPayment', ['VerifyCsrf']);
    Router::post('/admin/payments/{id}/refund', 'Admin\\PaymentController@refundPayment', ['VerifyCsrf']);
    Router::get('/admin/invoices/{id}', 'Admin\\PaymentController@showInvoice');

    // Reports & Analytics
    Router::get('/admin/reports', 'Admin\\ReportController@index');

    // Content (CMS)
    Router::get('/admin/content/faqs', 'Admin\\ContentController@faqs');
    Router::post('/admin/content/faqs', 'Admin\\ContentController@storeFaq', ['VerifyCsrf']);
    Router::post('/admin/content/faqs/{id}/delete', 'Admin\\ContentController@deleteFaq', ['VerifyCsrf']);

    Router::get('/admin/content/gallery', 'Admin\\ContentController@gallery');
    Router::post('/admin/content/gallery', 'Admin\\ContentController@storeGallery', ['VerifyCsrf']);
    Router::post('/admin/content/gallery/{id}/update', 'Admin\\ContentController@updateGallery', ['VerifyCsrf']);
    Router::post('/admin/content/gallery/{id}/delete', 'Admin\\ContentController@deleteGallery', ['VerifyCsrf']);
    Router::post('/admin/content/gallery/{id}/toggle', 'Admin\\ContentController@toggleGallery', ['VerifyCsrf']);

    Router::get('/admin/content/reviews', 'Admin\\ContentController@reviews');
    Router::post('/admin/content/reviews/{id}/approve', 'Admin\\ContentController@approveReview', ['VerifyCsrf']);
    Router::post('/admin/content/reviews/{id}/delete', 'Admin\\ContentController@deleteReview', ['VerifyCsrf']);

    Router::get('/admin/content/areas', 'Admin\\ContentController@areas');
    Router::post('/admin/content/areas', 'Admin\\ContentController@storeArea', ['VerifyCsrf']);
    Router::post('/admin/content/areas/{id}/update', 'Admin\\ContentController@updateArea', ['VerifyCsrf']);
    Router::post('/admin/content/areas/{id}/delete', 'Admin\\ContentController@deleteArea', ['VerifyCsrf']);
    Router::post('/admin/content/areas/{id}/toggle', 'Admin\\ContentController@toggleArea', ['VerifyCsrf']);

    Router::get('/admin/content/steps', 'Admin\\ContentController@steps');
    Router::post('/admin/content/steps/{id}', 'Admin\\ContentController@updateStep', ['VerifyCsrf']);

    Router::get('/admin/content/checklists', 'Admin\\ContentController@checklists');
    Router::post('/admin/content/checklists', 'Admin\\ContentController@storeChecklist', ['VerifyCsrf']);
    Router::post('/admin/content/checklists/reorder', 'Admin\\ContentController@reorderChecklists', ['VerifyCsrf']);
    Router::post('/admin/content/checklists/{id}/update', 'Admin\\ContentController@updateChecklist', ['VerifyCsrf']);
    Router::post('/admin/content/checklists/{id}/delete', 'Admin\\ContentController@deleteChecklist', ['VerifyCsrf']);

    // Settings
    Router::get('/admin/settings', 'Admin\\SettingsController@index');
    Router::post('/admin/settings', 'Admin\\SettingsController@save', ['VerifyCsrf']);
});

// ==========================================
// TECHNICIAN / STAFF PORTAL (/staff)
// ==========================================
Router::group(['middleware' => ['RequireRole:staff']], function () {
    Router::get('/staff', 'Staff\\DashboardController@index');
    Router::get('/staff/dashboard', 'Staff\\DashboardController@index');

    Router::get('/staff/jobs', 'Staff\\JobController@index');
    Router::get('/staff/jobs/{id}', 'Staff\\JobController@show');
    Router::post('/staff/jobs/{id}/accept', 'Staff\\JobController@accept', ['VerifyCsrf']);
    Router::post('/staff/jobs/{id}/on-the-way', 'Staff\\JobController@onTheWay', ['VerifyCsrf']);
    Router::post('/staff/jobs/{id}/start', 'Staff\\JobController@start', ['VerifyCsrf']);
    Router::post('/staff/jobs/{id}/photos', 'Staff\\JobController@uploadPhoto', ['VerifyCsrf']);
    Router::get('/staff/jobs/{id}/complete', 'Staff\\JobController@complete');
    Router::post('/staff/jobs/{id}/complete', 'Staff\\JobController@processComplete', ['VerifyCsrf']);
    Router::post('/staff/jobs/{id}/record-payment', 'Staff\\JobController@recordPayment', ['VerifyCsrf']);
    Router::post('/staff/jobs/{id}/create-payment-order', 'Staff\\JobController@createPaymentOrder', ['VerifyCsrf']);

    Router::get('/staff/earnings', 'Staff\\EarningsController@index');

    Router::get('/staff/profile', 'Staff\\ProfileController@index');
    Router::post('/staff/profile', 'Staff\\ProfileController@update', ['VerifyCsrf']);
});

// ==========================================
// API / AJAX JSON ENDPOINTS
// ==========================================
Router::post('/api/upload', 'Api\\UploadController@upload', ['VerifyCsrf']);
Router::post('/api/service-area/pincode', 'Api\\ServiceAreaController@checkPincode', ['VerifyCsrf']);
Router::post('/api/service-areas', 'Api\\ServiceAreaController@setCity', ['VerifyCsrf']);
Router::post('/api/set-city', 'Api\\ServiceAreaController@setCity', ['VerifyCsrf']);
Router::post('/api/search', 'Api\\ServiceAreaController@search', ['VerifyCsrf']);
Router::post('/api/job/status', 'Api\\JobStatusController@update', ['RequireLogin', 'VerifyCsrf']);
Router::get('/api/notifications', 'Api\\NotificationController@recent', ['RequireLogin']);
Router::post('/api/booking/record-dropoff', 'BookingController@recordDropoff');

// Authenticated Secure Document Serving (Outside Webroot)
Router::get('/documents/{folder}/{filename}', 'DocumentController@serve', ['RequireLogin']);

// Direct Service URLs under /services/ (e.g. /services/water-tank-cleaning)
Router::get('/services/{service}', 'ServiceController@serviceClean');
Router::get('/services/{service}-in-{city}', 'ServiceController@serviceInCity');

// Fallback for single clean service/category slugs (e.g. /full-home-cleaning, /bathroom-deep-cleaning)
Router::get('/{service}', 'ServiceController@serviceClean');

