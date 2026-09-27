<?php

return [
    'default' => env('MAIL_MAILER', 'smtp'),
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', 'smtp.gmail.com'),
            'port' => env('MAIL_PORT', 587),
            'encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'username' => env('MAIL_USERNAME', 'aman.ullah.csc@gmail.com'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => env('MAIL_TIMEOUT', 15),
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
            'verify_peer' => env('MAIL_VERIFY_PEER', false),
        ],
        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],
        'log' => ['transport' => 'log', 'channel' => env('MAIL_LOG_CHANNEL')],
        'array' => ['transport' => 'array'],
        'failover' => ['transport' => 'failover', 'mailers' => ['smtp', 'log'], 'retry_after' => 60],
    ],
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'amanullah@triplewtools.com'),
        'name' => env('MAIL_FROM_NAME', 'Amanullah Portfolio'),
    ],
];
