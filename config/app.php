<?php
declare(strict_types=1);

use App\Core\Env;

return [
    'name'     => Env::get('APP_NAME', 'REFIXEL'),
    'env'      => Env::get('APP_ENV', 'production'),
    'debug'    => (bool)Env::get('APP_DEBUG', false),
    'url'      => Env::get('APP_URL', 'http://localhost/website/public'),
    'robots'   => Env::get('APP_ROBOTS', (Env::get('APP_ENV') === 'staging' ? 'noindex, nofollow' : 'index, follow')),
    'timezone' => Env::get('APP_TIMEZONE', 'Asia/Kolkata'),
    'company'  => [
        'name'    => Env::get('COMPANY_NAME', 'REFIXEL Home Services'),
        'phone'   => Env::get('COMPANY_PHONE', '+91 87911 54730'),
        'email'   => Env::get('COMPANY_EMAIL', 'contact@refixel.com'),
        'address' => Env::get('COMPANY_ADDRESS', 'Jaspur - Kashipur Road, in front of BSV Girls Degree College, Jaspur, Uttarakhand, 244712'),
        'gstin'   => Env::get('COMPANY_GSTIN', '07AAAAA0000A1Z5'),
    ],
];

