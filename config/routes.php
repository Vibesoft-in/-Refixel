<?php
declare(strict_types=1);

use App\Core\Router;

// ==========================================
// PUBLIC CUSTOMER ROUTES
// ==========================================
Router::get('/', 'HomeController@index');
Router::get('/index.php', 'HomeController@index'); // Legacy alias

Router::get('/about', 'PageController@about');
Router::get('/about-us.php', 'PageController@about');

Router::get('/faq', 'PageController@faq');
Router::get('/faqs.php', 'PageController@faq');

Router::get('/gallery', 'PageController@gallery');

Router::get('/contact', 'PageController@contact');
Router::get('/contact-us.php', 'PageController@contact');
Router::post('/contact', 'PageController@submitContact');

Router::get('/terms', 'PageController@terms');
Router::get('/terms-and-conditions.php', 'PageController@terms');

Router::get('/privacy', 'PageController@privacy');
Router::get('/privacy-policy.php', 'PageController@privacy');

Router::get('/refund', 'PageController@refund');
Router::get('/refund-policy.php', 'PageController@refund');

Router::get('/blog', 'PageController@blog');
Router::get('/blog.php', 'PageController@blog');

Router::get('/partner', 'PageController@partner');
Router::post('/partner', 'PageController@submitPartner');

Router::get('/services', 'ServiceController@index');

Router::get('/sitemap.xml', 'PageController@sitemap');
Router::get('/robots.txt', 'PageController@robots');


// Clean URLs for categories (e.g. /cleaning-services, /painting-services)
Router::get('/{category}-services', 'ServiceController@categoryClean');

// SEO URLs for categories and services with city (backward-compatible)
Router::get('/{category}-services-in-{city}', 'ServiceController@categoryInCity');
Router::get('/{service}-in-{city}', 'ServiceController@serviceInCity');

// Booking Flow & Cart
Router::get('/book', 'BookingController@showForm');
Router::post('/book', 'BookingController@submit', ['VerifyCsrf']);
Router::get('/book-success', 'BookingController@success');

Router::get('/cart', 'BookingController@cart');
Router::get('/cart.php', 'BookingController@cart');
Router::get('/api/cart', 'BookingController@apiCart');
Router::post('/api/cart/add', 'BookingController@addToCart');
Router::post('/api/cart/update', 'BookingController@updateCart');
Router::post('/api/cart/remove', 'BookingController@removeFromCart');
Router::post('/api/cart/clear', 'BookingController@clearCart');
Router::get('/api/services', 'ServiceController@apiSearch');

// Legacy ajax endpoints compatibility
Router::post('/cartajax/ajax_cart_add.php', 'BookingController@addToCart');
Router::post('/cartajax/ajax_cart_update.php', 'BookingController@updateCart');


// ==========================================
// UNIFIED AUTHENTICATION (ALL ROLES)
// ==========================================
Router::get('/login', 'AuthController@showLogin');
Router::post('/login', 'AuthController@login', ['VerifyCsrf', 'RateLimit:5,60']);

Router::get('/signup', 'AuthController@showSignup');
Router::post('/signup', 'AuthController@signup', ['VerifyCsrf', 'RateLimit:5,60']);

Router::get('/forgot-password', 'AuthController@showForgotPassword');
Router::post('/forgot-password', 'AuthController@forgotPassword', ['VerifyCsrf', 'RateLimit:5,60']);

Router::get('/reset-password', 'AuthController@showResetPassword');
Router::post('/reset-password', 'AuthController@resetPassword', ['VerifyCsrf']);

Router::get('/change-password', 'AuthController@showChangePassword', ['RequireLogin']);
Router::post('/change-password', 'AuthController@changePassword', ['RequireLogin', 'VerifyCsrf']);

Router::get('/logout', 'AuthController@logout');
Router::get('/logout.php', 'AuthController@logout');

// ==========================================
// CUSTOMER PORTAL (/account)
// ==========================================
Router::group(['middleware' => ['RequireRole:customer']], function () {
    Router::get('/account', 'AccountController@index');
    Router::get('/account/bookings', 'AccountController@bookings');
    Router::get('/my-booking.php', 'AccountController@bookings'); // Legacy alias
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
// ADMIN PORTAL (/admin)
// ==========================================
Router::group(['middleware' => ['RequireRole:admin']], function () {
    Router::get('/admin', 'Admin\\DashboardController@index');
    Router::get('/admin/dashboard', 'Admin\\DashboardController@index');

    // Enquiries
    Router::get('/admin/enquiries', 'Admin\\EnquiryController@index');
    Router::get('/admin/enquiries/{id}', 'Admin\\EnquiryController@show');
    Router::post('/admin/enquiries/{id}/update', 'Admin\\EnquiryController@update', ['VerifyCsrf']);

    // Bookings & Dispatch Calendar
    Router::get('/admin/bookings', 'Admin\\BookingController@index');
    Router::get('/admin/bookings/{id}', 'Admin\\BookingController@show');
    Router::post('/admin/bookings/{id}/assign', 'Admin\\BookingController@assignStaff', ['VerifyCsrf']);
    Router::post('/admin/bookings/{id}/reschedule', 'Admin\\BookingController@reschedule', ['VerifyCsrf']);
    Router::post('/admin/bookings/{id}/status', 'Admin\\BookingController@updateStatus', ['VerifyCsrf']);
    Router::get('/admin/calendar', 'Admin\\BookingController@calendar');

    // Staff
    Router::get('/admin/staff', 'Admin\\StaffController@index');
    Router::get('/admin/staff/create', 'Admin\\StaffController@create');
    Router::post('/admin/staff', 'Admin\\StaffController@store', ['VerifyCsrf']);
    Router::get('/admin/staff/{id}', 'Admin\\StaffController@show');
    Router::post('/admin/staff/{id}/toggle-status', 'Admin\\StaffController@toggleStatus', ['VerifyCsrf']);
    Router::post('/admin/staff/{id}/skills', 'Admin\\StaffController@updateSkills', ['VerifyCsrf']);

    // Services & Categories
    Router::get('/admin/services', 'Admin\\ServiceController@index');
    Router::post('/admin/services', 'Admin\\ServiceController@storeService', ['VerifyCsrf']);
    Router::post('/admin/services/{id}/update', 'Admin\\ServiceController@updateService', ['VerifyCsrf']);
    Router::post('/admin/services/{id}/delete', 'Admin\\ServiceController@deleteService', ['VerifyCsrf']);
    Router::post('/admin/services/{id}/toggle-status', 'Admin\\ServiceController@toggleServiceStatus', ['VerifyCsrf']);
    Router::get('/admin/services/categories', 'Admin\\ServiceController@categories');
    Router::post('/admin/services/categories', 'Admin\\ServiceController@storeCategory', ['VerifyCsrf']);
    Router::post('/admin/services/categories/{id}/update', 'Admin\\ServiceController@updateCategory', ['VerifyCsrf']);
    Router::post('/admin/services/categories/{id}/delete', 'Admin\\ServiceController@deleteCategory', ['VerifyCsrf']);
    Router::post('/admin/services/categories/{id}/toggle-status', 'Admin\\ServiceController@toggleCategoryStatus', ['VerifyCsrf']);

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

    Router::get('/staff/earnings', 'Staff\\EarningsController@index');

    Router::get('/staff/profile', 'Staff\\ProfileController@index');
    Router::post('/staff/profile', 'Staff\\ProfileController@update', ['VerifyCsrf']);
});

// ==========================================
// API / AJAX JSON ENDPOINTS
// ==========================================
Router::post('/api/upload', 'Api\\UploadController@upload', ['VerifyCsrf']);
Router::post('/api/service-area/pincode', 'Api\\ServiceAreaController@checkPincode');
Router::post('/api/search', 'Api\\ServiceAreaController@search');
Router::post('/api/job/status', 'Api\\JobStatusController@update', ['RequireLogin', 'VerifyCsrf']);
Router::get('/api/notifications', 'Api\\NotificationController@recent', ['RequireLogin']);

// Fallback for single clean service/category slugs (e.g. /full-home-cleaning, /bathroom-deep-cleaning)
Router::get('/{service}', 'ServiceController@serviceClean');
