<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Bu komut yalnizca CLI uzerinden calistirilabilir.\n");
    exit(1);
}

$name = trim((string) ($argv[1] ?? ''));

if ($name === '') {
    fwrite(STDERR, "Kullanim: php dev/bin/make-migration.php create_users_table\n");
    exit(1);
}

$normalized = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '_', $name), '_'));
$timestamp = date('Ymd_His');
$projectRoot = dirname(__DIR__, 2);
$directory = $projectRoot . '/database/migrations';
$file = $directory . '/' . $timestamp . '_' . $normalized . '.sql';

if (! is_dir($directory)) {
    mkdir($directory, 0777, true);
}

$template = "-- Migration: {$normalized}\n-- Created at: {$timestamp}\n\n-- up\n\n-- down\n";
file_put_contents($file, $template);

echo "Olusturuldu: {$file}\n";
