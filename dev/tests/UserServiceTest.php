<?php

declare(strict_types=1);

use App\Services\AuthorizationService;
use App\Services\IdentityService;
use App\Services\UserService;

require_once __DIR__ . '/bootstrap.php';

$pdo = testPdo();
$timestamp = time();
$pdo->exec("INSERT INTO userRole (id, name, auth, created_at, updated_at) VALUES (1, 'Admin', 'all', {$timestamp}, {$timestamp})");
$pdo->exec("INSERT INTO userRole (id, name, auth, created_at, updated_at) VALUES (2, 'User', 'profile', {$timestamp}, {$timestamp})");

$authorization = new AuthorizationService();
$service = new UserService(new IdentityService($authorization, [
    'header' => 'X-Api-Token',
    'token_ttl' => 600,
]), $authorization);

$create = $service->createUser([
    'name' => 'Ada',
    'surname' => 'Lovelace',
    'email' => 'ada@example.com',
    'password' => '010101',
    'role' => 2,
    'status' => 'active',
    'phone' => '5550000000',
    'city' => 'London',
    'address' => 'Analytical Engine Street',
]);
assertTrue($create->isSuccess(), 'Kullanici olusturma basarili olmali.');

$list = $service->listUsers();
assertTrue($list->isSuccess(), 'Kullanici listeleme basarili olmali.');
assertSame(1, count((array) ($list->data()['users'] ?? [])), 'Liste bir kullanici icermeli.');

$update = $service->updateRole(2, [
    'name' => 'Member',
    'auth' => 'profile,users.view',
]);
assertTrue($update->isSuccess(), 'Rol guncelleme basarili olmali.');

echo "UserServiceTest ok\n";
