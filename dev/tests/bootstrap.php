<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'App\\' => dirname(__DIR__, 2) . '/app/',
        'Core\\' => dirname(__DIR__, 2) . '/core/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (! str_starts_with($class, $prefix)) {
            continue;
        }

        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (is_file($file)) {
            require $file;
        }
    }
});

use Core\Orm\Models;
function testPdo(): PDO
{
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('CREATE TABLE userRole (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, auth TEXT NOT NULL, created_at INTEGER NOT NULL, updated_at INTEGER NOT NULL)');
    $pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, surname TEXT NOT NULL, email TEXT NOT NULL UNIQUE, password TEXT NOT NULL, role INTEGER NOT NULL, status TEXT NOT NULL, created_at INTEGER NOT NULL, updated_at INTEGER NOT NULL)');
    $pdo->exec('CREATE TABLE userProfile (id INTEGER PRIMARY KEY AUTOINCREMENT, userId INTEGER NOT NULL, phone TEXT, city TEXT, address TEXT, created_at INTEGER NOT NULL, updated_at INTEGER NOT NULL)');
    $pdo->exec('CREATE TABLE api_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NULL, name TEXT NOT NULL, token TEXT NOT NULL, header_name TEXT NOT NULL, type TEXT NOT NULL, is_active INTEGER NOT NULL, expires_at INTEGER NULL, last_used_at INTEGER NULL, created_at INTEGER NOT NULL, updated_at INTEGER NOT NULL)');

    Models::setDb($pdo);
    Models::setSchema('main');

    return $pdo;
}

function assertTrue(bool $condition, string $message): void
{
    if (! $condition) {
        throw new RuntimeException($message);
    }
}

function assertSame(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message . ' Expected: ' . var_export($expected, true) . ' Actual: ' . var_export($actual, true));
    }
}
