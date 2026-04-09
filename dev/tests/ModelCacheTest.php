<?php

declare(strict_types=1);

use Core\Orm\Models;

require_once __DIR__ . '/bootstrap.php';

testPdo();

$cacheRoot = sys_get_temp_dir() . '/miu-model-cache-' . bin2hex(random_bytes(6));
$namespaceDir = $cacheRoot . '/test-suite';

if (! is_dir($namespaceDir) && ! mkdir($namespaceDir, 0777, true) && ! is_dir($namespaceDir)) {
    throw new RuntimeException('Model cache test klasoru olusturulamadi.');
}

Models::setModelCacheConfig([
    'enabled' => true,
    'refresh' => false,
    'path' => $cacheRoot,
    'namespace' => 'test-suite',
]);

$users = Models::get('users');
$usersCacheFile = $namespaceDir . '/sqlite-main-users.json';

assertTrue(is_file($usersCacheFile), 'Mevcut tablo icin model cache dosyasi uretilmeli.');

$phantomCacheFile = $namespaceDir . '/sqlite-main-phantom.json';
file_put_contents($phantomCacheFile, json_encode([
    'driver' => 'sqlite',
    'schema' => 'main',
    'table' => 'phantom',
    'generated_at' => time(),
    'columns' => [
        ['name' => 'id', 'type' => 'INTEGER'],
        ['name' => 'title', 'type' => 'TEXT'],
    ],
], JSON_PRETTY_PRINT));

$phantom = Models::get('phantom');
assertTrue(property_exists($phantom, 'title'), 'Cache var ise olmayan tablo metadata dosyadan okunabilmeli.');

Models::setModelCacheConfig([
    'enabled' => true,
    'refresh' => true,
    'path' => $cacheRoot,
    'namespace' => 'test-suite',
]);

$refreshFailed = false;

try {
    Models::get('phantom');
} catch (RuntimeException) {
    $refreshFailed = true;
}

assertTrue($refreshFailed, 'Refresh aciksa cache atlanip tablo dogrudan veritabanindan cozulmeli.');

echo "ModelCacheTest ok\n";
