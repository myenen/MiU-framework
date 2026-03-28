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
$database = new Database($databaseConfig);
$pdo = $database->pdo();
$migrationsPath = $basePath . '/database/migrations';

if (! is_dir($migrationsPath)) {
    fwrite(STDOUT, "Migration klasoru bulunamadi.\n");
    exit(0);
}

ensureMigrationTable($pdo);
$executed = $pdo->query('SELECT migration FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN) ?: [];
$files = glob($migrationsPath . '/*.sql') ?: [];
sort($files);

foreach ($files as $file) {
    $name = basename($file);

    if (in_array($name, $executed, true)) {
        continue;
    }

    $sql = migrationSection((string) file_get_contents($file), 'up');
    if ($sql === '') {
        continue;
    }

    $pdo->beginTransaction();

    try {
        $pdo->exec($sql);
        $stmt = $pdo->prepare('INSERT INTO schema_migrations (migration, executed_at) VALUES (:migration, :executed_at)');
        $stmt->execute([
            ':migration' => $name,
            ':executed_at' => time(),
        ]);
        $pdo->commit();
        fwrite(STDOUT, "Uygulandi: {$name}\n");
    } catch (Throwable $exception) {
        $pdo->rollBack();
        fwrite(STDERR, "Hata: {$name} - {$exception->getMessage()}\n");
        exit(1);
    }
}

echo "Migration islemi tamamlandi.\n";

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
