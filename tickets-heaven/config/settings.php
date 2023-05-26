<?php

return [

    'app' => [

        'url' => $_ENV['APP_URL'],

        'name' => $_ENV['APP_NAME'],

        'profile_pictures_folder' => $_ENV['APP_PROFILE_PICTURES_FOLDER'],

        'profile_pictures_max_width' => $_ENV['APP_PROFILE_PICTURES_MAX_WIDTH'],

        'profile_pictures_max_height' => $_ENV['APP_PROFILE_PICTURES_MAX_HEIGHT'],

        'venue_pictures_folder' => $_ENV['APP_VENUE_PICTURES_FOLDER'],

        'venue_pictures_max_width' => $_ENV['APP_VENUE_PICTURES_MAX_WIDTH'],

        'venue_pictures_max_height' => $_ENV['APP_VENUE_PICTURES_MAX_HEIGHT'],

        'event_pictures_folder' => $_ENV['APP_EVENT_PICTURES_FOLDER'],

        'event_pictures_max_width' => $_ENV['APP_EVENT_PICTURES_MAX_WIDTH'],

        'event_pictures_max_height' => $_ENV['APP_EVENT_PICTURES_MAX_HEIGHT'],

        'exchange_rate_api_endpoint' => $_ENV['APP_EXCHANGE_RATE_API_ENDPOINT'],

        'default_currency' => $_ENV['APP_DEFAULT_CURRENCY'],
    ],

    'auth' => [

        'remember' => $_ENV['AUTH_REMEMBER'],

        'github' => [

            'client_id' => $_ENV['AUTH_GITHUB_CLIENT_ID'],

            'client_secret' => $_ENV['AUTH_GITHUB_CLIENT_SECRET'],

            'redirect_uri' => $_ENV['AUTH_GITHUB_REDIRECT_URI'],
        ],

        'facebook' => [

            'client_id' => $_ENV['AUTH_FACEBOOK_CLIENT_ID'],

            'client_secret' => $_ENV['AUTH_FACEBOOK_CLIENT_SECRET'],

            'redirect_uri' => $_ENV['AUTH_FACEBOOK_REDIRECT_URI'],
        ],
    ],

    'db' => [

        'driver' => $_ENV['DB_DRIVER'],

        'host' => $_ENV['DB_HOST'],

        'port' => $_ENV['DB_PORT'],

        'database' => $_ENV['DB_NAME'],

        'username' => $_ENV['DB_USERNAME'],

        'password' => $_ENV['DB_PASSWORD'],

        'charset' => $_ENV['DB_CHARSET'],

        'collation' => $_ENV['DB_COLLATION'],

        'prefix' => $_ENV['DB_PREFIX'],
    ],

    'mail' => [

        'host' => $_ENV['MAIL_HOST'],

        'charset' => $_ENV['MAIL_CHARSET'],

        'smtp_auth' => $_ENV['MAIL_SMTP_AUTH'],

        'smtp_secure' => $_ENV['MAIL_SMTP_SECURE'],

        'port' => $_ENV['MAIL_PORT'],

        'username' => $_ENV['MAIL_USERNAME'],

        'password' => $_ENV['MAIL_PASSWORD'],

        'html' => $_ENV['MAIL_HTML'],
        
        'support' => $_ENV['MAIL_SUPPORT'],

        'disable' => $_ENV['MAIL_DISABLE'],
    ]
];
