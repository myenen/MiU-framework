<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/config/bootstrap.php';

use Core\Database;

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Bu komut yalnizca CLI uzerinden calistirilabilir.\n");
    exit(1);
}

$basePath = dirname(__DIR__, 2);
$config = require $basePath . '/config/app.php';
$databaseConfig = resolveDatabaseConfig($config['database'] ?? [], (string) ($config['environment'] ?? 'local'));
$pdo = (new Database($databaseConfig))->pdo();
$migrationsPath = $basePath . '/database/migrations';

ensureMigrationTable($pdo);
$lastMigration = $pdo->query('SELECT migration FROM schema_migrations ORDER BY id DESC LIMIT 1')->fetchColumn();

if (! is_string($lastMigration) || $lastMigration === '') {
    fwrite(STDOUT, "Geri alinacak migration bulunamadi.\n");
    exit(0);
}

$file = $migrationsPath . '/' . $lastMigration;

if (! is_file($file)) {
    fwrite(STDERR, "Migration dosyasi bulunamadi: {$lastMigration}\n");
    exit(1);
}

$sql = migrationSection((string) file_get_contents($file), 'down');

if ($sql === '') {
    fwrite(STDERR, "Down bolumu bos: {$lastMigration}\n");
    exit(1);
}

$pdo->beginTransaction();

try {
    $pdo->exec($sql);
    $stmt = $pdo->prepare('DELETE FROM schema_migrations WHERE migration = :migration');
    $stmt->execute([':migration' => $lastMigration]);
    $pdo->commit();
    fwrite(STDOUT, "Geri alindi: {$lastMigration}\n");
} catch (Throwable $exception) {
    $pdo->rollBack();
    fwrite(STDERR, "Hata: {$lastMigration} - {$exception->getMessage()}\n");
    exit(1);
}

function ensureMigrationTable(PDO $pdo): void
{
    $driver = (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

    $sql = match ($driver) {
        'sqlite' => 'CREATE TABLE IF NOT EXISTS schema_migrations (id INTEGER PRIMARY KEY AUTOINCREMENT, migration TEXT NOT NULL UNIQUE, executed_at INTEGER NOT NULL)',
        'pgsql' => 'CREATE TABLE IF NOT EXISTS schema_migrations (id BIGSERIAL PRIMARY KEY, migration VARCHAR(255) NOT NULL UNIQUE, executed_at BIGINT NOT NULL)',
        default => 'CREATE TABLE IF NOT EXISTS schema_migrations (id INT UNSIGNED NOT NULL AUTO_INCREMENT, migration VARCHAR(255) NOT NULL UNIQUE, executed_at BIGINT UNSIGNED NOT NULL, PRIMARY KEY (id))',
    };

    $pdo->exec($sql);
}

function migrationSection(string $sql, string $section): string
{
    $normalized = str_replace("\r\n", "\n", $sql);
    $pattern = sprintf('/--\s*%s\s*(.*?)(?:--\s*(?:up|down)\s*|\z)/si', preg_quote($section, '/'));

    if (preg_match($pattern, $normalized, $matches) === 1) {
        return trim((string) ($matches[1] ?? ''));
    }

    return trim($normalized);
}
