<?php

declare(strict_types=1);

use App\Services\FileUploadService;

require_once __DIR__ . '/bootstrap.php';

$pdo = testPdo();
$pdo->exec('CREATE TABLE uploads (id INTEGER PRIMARY KEY AUTOINCREMENT, channel TEXT NOT NULL, original_name TEXT NOT NULL, stored_name TEXT NOT NULL, directory_name TEXT NOT NULL, mime_type TEXT, extension TEXT, size INTEGER NOT NULL, public_path TEXT NOT NULL, created_at INTEGER NOT NULL)');

$root = sys_get_temp_dir() . '/php-framework-upload-test-' . bin2hex(random_bytes(6));
$service = new FileUploadService($root);

$tmpOne = tempnam(sys_get_temp_dir(), 'upl');
file_put_contents($tmpOne, 'first');

$first = $service->uploadFile([
    'error' => UPLOAD_ERR_OK,
    'name' => 'Logo File.png',
    'tmp_name' => $tmpOne,
    'size' => filesize($tmpOne),
    'type' => 'image/png',
], 'site');

assertTrue(str_contains($first['directory'], 'site/'), 'Dizin kanal adini icermeli.');
assertTrue((bool) preg_match('#^site/\d{4}/\d{2}/\d{2}$#', $first['directory']), 'Dizin yil/ay/gun formatinda olmali.');
assertSame('logo-file.png', $first['stored_name'], 'Ilk dosya orijinal isimden turetilmeli.');

$tmpTwo = tempnam(sys_get_temp_dir(), 'upl');
file_put_contents($tmpTwo, 'second');

$second = $service->uploadFile([
    'error' => UPLOAD_ERR_OK,
    'name' => 'Logo File.png',
    'tmp_name' => $tmpTwo,
    'size' => filesize($tmpTwo),
    'type' => 'image/png',
], 'site');

assertSame('logo-file_1.png', $second['stored_name'], 'Ayni klasorde cakisanda sonuna sira numarasi eklenmeli.');
assertTrue(file_exists($second['path']), 'Yuklenen ikinci dosya fiziksel olarak mevcut olmali.');

echo "FileUploadServiceTest ok\n";
