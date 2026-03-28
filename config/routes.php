<?php

declare(strict_types=1);

use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\LoginController;
use App\Controllers\Admin\Users;
use App\Controllers\Api\Auth as ApiAuthController;
use App\Controllers\Api\StatusController;
use App\Controllers\Site\HomeController;
use App\Controllers\Site\LoginController as SiteLoginController;

/**
 * Dinamik rota cozumunden once yuklenen statik rota tanimlari.
 */
return [
    ['GET', '/', [HomeController::class, 'index']],
    ['GET', '/login', [SiteLoginController::class, 'show']],
    ['POST', '/login', [SiteLoginController::class, 'login']],
    ['POST', '/logout', [SiteLoginController::class, 'logout']],
    ['POST', '/api/v1/auth/login', [ApiAuthController::class, 'login']],
    ['GET', '/api/v1/status', [StatusController::class, 'index']],
    ['GET', '/api/v1/secure-status', [StatusController::class, 'secure']],
    ['GET', '/admin/login', [LoginController::class, 'show']],
    ['POST', '/admin/login', [LoginController::class, 'login']],
    ['POST', '/admin/logout', [LoginController::class, 'logout']],
    ['GET', '/admin', [DashboardController::class, 'index']],
    ['GET', '/admin/users', [Users::class, 'index']],
    ['GET', '/admin/users/create', [Users::class, 'create']],
    ['POST', '/admin/users/create', [Users::class, 'store']],
];
