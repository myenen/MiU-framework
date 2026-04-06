<?php

declare(strict_types=1);

use Core\Application;
use Core\Container;
use Core\Http\Request;

require_once __DIR__ . '/bootstrap.php';

$app = new Application(
    new Container(),
    new Request('GET', '/login'),
    [],
    [],
    [],
    [
        'maintenance' => [
            'enabled' => true,
            'status' => 503,
            'message' => 'Bakim var.',
            'retry_after' => 120,
            'allowed_paths' => [],
            'allowed_ips' => [],
        ],
        'headers' => [],
    ]
);

$response = $app->handle();

assertSame(503, $response->status(), 'Bakim modu 503 donmeli.');
assertTrue(str_contains($response->body(), 'Bakim var.'), 'Bakim modu mesaji HTML icinde yer almali.');

echo "MaintenanceModeTest ok\n";
