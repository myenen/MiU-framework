<?php

declare(strict_types=1);

use App\Services\AuthorizationService;

require_once __DIR__ . '/bootstrap.php';

$pdo = testPdo();
$timestamp = time();
$pdo->exec("INSERT INTO userRole (id, name, auth, created_at, updated_at) VALUES (1, 'Admin', 'all', {$timestamp}, {$timestamp})");
$pdo->exec("INSERT INTO userRole (id, name, auth, created_at, updated_at) VALUES (2, 'Editor', 'dashboard.view,users.view', {$timestamp}, {$timestamp})");

$service = new AuthorizationService();

assertTrue($service->identityCan(['permissions' => ['all']], 'users.edit'), 'all yetkisi users.edit gecirmeli.');
assertTrue($service->identityCan(['permissions' => ['users.*']], 'users.delete'), 'users.* wildcard calismali.');
assertTrue($service->identityCan(['permissions' => ['dashboard.view', 'users.view']], ['users.edit', 'users.view']), 'Coklu permission kontrolu calismali.');
assertSame(['dashboard.view', 'users.view'], $service->permissionsForRole(2), 'Rol yetkileri veritabanindan okunmali.');

echo "AuthorizationServiceTest ok\n";
