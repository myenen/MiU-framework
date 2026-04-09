<?php

declare(strict_types=1);

/**
 * Ana proje yapilandirma dosyasi.
 */
return [
    'name' => 'MiU',
    'environment' => 'local',
    'app' => [
        'active' => 'local',
        'urls' => [
            'local' => 'http://localhost:8000',
            'server' => 'https://example.com',
        ],
    ],
    'assets' => [
        'path' => '/assets',
    ],
    'cache' => [
        'ttl' => 60,
    ],
    'session' => [
        'name' => 'MIUSESSID',
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'http_only' => true,
        'same_site' => 'Lax',
    ],
    'debug' => [
        'enabled' => true,
        'log_file' => '',
    ],
    'security' => [
        'headers' => [
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
        ],
        'maintenance' => [
            'enabled' => false,
            'status' => 503,
            'message' => 'Sistem gecici olarak bakimdadir. Lutfen daha sonra tekrar deneyin.',
            'retry_after' => 600,
            'allowed_paths' => [
                '/api/v1/status',
            ],
            'allowed_ips' => [],
        ],
        'login_rate_limit' => [
            'site' => [
                'enabled' => true,
                'max_attempts' => 5,
                'decay_seconds' => 300,
            ],
            'admin' => [
                'enabled' => true,
                'max_attempts' => 5,
                'decay_seconds' => 300,
            ],
        ],
    ],
    'logging' => [
        'request_log' => [
            'enabled' => true,
            'max_body_length' => 4000,
            'max_user_agent_length' => 500,
            'mask_fields' => [
                'password',
                'password_hash',
                'token',
                'api_token',
                'authorization',
                'x-api-token',
                'x-csrf-token',
            ],
            'skip_response_body_paths' => [
                '/login',
                '/admin/login',
                '/api/v1/auth/login',
            ],
        ],
    ],
    'view' => [
        'site_layout' => 'layouts/main',
        'admin_layout' => 'layouts/main',
    ],
    'routing' => [
        'dynamic' => true,
        'default_method' => 'index',
        'namespaces' => [
            'site' => 'App\\Controllers\\Site',
            'admin' => 'App\\Controllers\\Admin',
            'api' => 'App\\Controllers\\Api',
        ],
    ],
    'mail' => [
        'site_layout' => 'layouts/main',
        'admin_layout' => 'layouts/main',
        'mailer' => 'smtp',
        'host' => 'smtp.example.com',
        'port' => 587,
        'username' => 'noreply@example.com',
        'password' => 'change-this-mail-password',
        'encryption' => 'tls',
        'auth' => true,
        'from_email' => 'noreply@example.com',
        'from_name' => 'MiU',
        'reply_to' => '',
        'reply_to_name' => '',
        'timeout' => 15,
        'debug' => 0,
    ],
    'upload' => [
        'max_size' => 5 * 1024 * 1024,
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'zip'],
        'allowed_mime_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'application/zip',
            'application/x-zip-compressed',
        ],
        'channels' => [
            'images' => [
                'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                'allowed_mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            ],
            'documents' => [
                'allowed_extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt'],
                'allowed_mime_types' => [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'text/plain',
                ],
            ],
        ],
    ],
    'api' => [
        'header' => 'X-Api-Token',
        'token_ttl' => 600,
        'rate_limit' => [
            'enabled' => true,
            'max_attempts' => 120,
            'decay_seconds' => 60,
            'login_max_attempts' => 10,
            'login_decay_seconds' => 60,
        ],
    ],
    'localization' => [
        'default_locale' => 'tr',
    ],
    'database' => [
        'active' => 'local',
        'model_cache' => [
            'enabled' => true,
            'refresh' => false,
            'path' => dirname(__DIR__) . '/storage/cache/models',
        ],
        'connections' => [
            'local' => [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'port' => 3306,
                'database' => 'mini_framework',
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8mb4',
                'schema' => 'public',
                'sqlite_database' => dirname(__DIR__) . '/database/app.sqlite',
                'schema_file' => dirname(__DIR__) . '/database/schema.sql',
                'seed_file' => dirname(__DIR__) . '/database/seed.sql',
                'options' => [],
            ],
            'server' => [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'port' => 3306,
                'database' => 'sunucu_veritabani_adi',
                'username' => 'sunucu_kullanici_adi',
                'password' => 'sunucu_sifre',
                'charset' => 'utf8mb4',
                'schema' => 'public',
                'sqlite_database' => dirname(__DIR__) . '/database/app.sqlite',
                'schema_file' => dirname(__DIR__) . '/database/schema.sql',
                'seed_file' => dirname(__DIR__) . '/database/seed.sql',
                'options' => [],
            ],
        ],
    ],
];
