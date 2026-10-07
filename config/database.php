<?php
declare(strict_types=1);

use App\Core\Env;

return [
    'default' => 'mysql',
    'connections' => [
        'mysql' => [
            'host'     => Env::get('DB_HOST', '127.0.0.1'),
            'port'     => (int)Env::get('DB_PORT', 3306),
            'database' => Env::get('DB_DATABASE', 'u537268535_refixel_db'),
            'username' => Env::get('DB_USERNAME', 'u537268535_refixel_db'),
            'password' => Env::get('DB_PASSWORD', 'Vibesoft@#2021'),
            'charset'  => Env::get('DB_CHARSET', 'utf8mb4'),
        ],
    ],
];
