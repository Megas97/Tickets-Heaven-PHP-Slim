<?php

require_once "root.php";

require_once SITE_ROOT . '/bootstrap/app.php';

return [
    'paths' => [
        'migrations' => 'database/migrations',
        'seeds' => 'database/seeds'
    ],
    'migration_base_class' => 'migrations\Migration',
    'templates' => [
        'file' => 'src/migrations/MigrationStub.php'
    ],
    'environments' => [
        'default_migration_table' => 'migrations',
        'default' => [
            'adapter' => $settings['db']['driver'],
            'host' => $settings['db']['host'],
            'port' > $settings['db']['port'],
            'name' => $settings['db']['database'],
            'user' => $settings['db']['username'],
            'pass' => $settings['db']['password'],
        ]
    ]
];
