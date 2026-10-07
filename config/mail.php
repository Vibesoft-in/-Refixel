<?php
declare(strict_types=1);

use App\Core\Env;

return [
    'mailer'     => Env::get('MAIL_MAILER', 'smtp'),
    'host'       => Env::get('MAIL_HOST', 'smtp.mailtrap.io'),
    'port'       => (int)Env::get('MAIL_PORT', 2525),
    'username'   => Env::get('MAIL_USERNAME', ''),
    'password'   => Env::get('MAIL_PASSWORD', ''),
    'encryption' => Env::get('MAIL_ENCRYPTION', 'tls'),
    'from'       => [
        'address' => Env::get('MAIL_FROM_ADDRESS', 'bookings@REFIXEL.com'),
        'name'    => Env::get('MAIL_FROM_NAME', 'REFIXEL Service Platform'),
    ],
];

