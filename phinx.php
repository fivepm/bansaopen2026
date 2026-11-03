<?php
// Konfigurasi Phinx - kredensial dibaca dari .env (tidak ada hardcode)
require __DIR__ . '/vendor/autoload.php';

Dotenv\Dotenv::createImmutable(__DIR__)->safeLoad();

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds'      => '%%PHINX_CONFIG_DIR%%/db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_environment'     => 'default',
        'default' => [
            'adapter'   => 'mysql',
            'host'      => $_ENV['DB_HOST'] ?? '127.0.0.1',
            'name'      => $_ENV['DB_NAME'] ?? '',
            'user'      => $_ENV['DB_USER'] ?? 'root',
            'pass'      => $_ENV['DB_PASS'] ?? '',
            'port'      => (int)($_ENV['DB_PORT'] ?? 3306),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
    ],
    'version_order' => 'creation',
];

