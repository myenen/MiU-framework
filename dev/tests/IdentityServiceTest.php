<?php

declare(strict_types=1);

use App\Services\AuthorizationService;
use App\Services\IdentityService;

require_once __DIR__ . '/bootstrap.php';

$pdo = testPdo();
$timestamp = time();
$authorization = new AuthorizationService();
$identity = new IdentityService($authorization, [
    'header' => 'X-Api-Token',
    'token_ttl' => 600,
]);
$hash = $identity->hashUserPassword('010101');

$stmt = $pdo->prepare('INSERT INTO userRole (id, name, auth, created_at, updated_at) VALUES (1, :name, :auth, :created_at, :updated_at)');
$stmt->execute([
    ':name' => 'Admin',
    ':auth' => 'all',
    ':created_at' => $timestamp,
    ':updated_at' => $timestamp,
]);

$stmt = $pdo->prepare('INSERT INTO users (name, surname, email, password, role, status, created_at, updated_at) VALUES (:name, :surname, :email, :password, :role, :status, :created_at, :updated_at)');
$stmt->execute([
    ':name' => 'Test',
    ':surname' => 'Admin',
    ':email' => 'admin@example.com',
    ':password' => $hash,
    ':role' => 1,
    ':status' => 'active',
    ':created_at' => $timestamp,
    ':updated_at' => $timestamp,
]);

assertTrue($identity->verifyUserPassword('010101', $hash), 'Password verify calismali.');
$auth = $identity->authenticateAdminCredentials('admin@example.com', '010101');
assertTrue($auth->isSuccess(), 'Admin login basarili olmali.');

$apiLogin = $identity->loginUserForApi('admin@example.com', '010101', 'Test Device');
assertTrue($apiLogin->isSuccess(), 'API login basarili olmali.');
assertTrue(((string) ($apiLogin->data()['token'] ?? '')) !== '', 'API token uretilmeli.');

echo "IdentityServiceTest ok\n";
