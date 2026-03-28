<?php

declare(strict_types=1);

use Core\Orm\Models;

require_once __DIR__ . '/bootstrap.php';

$pdo = testPdo();
$timestamp = time();

$pdo->exec(
    "INSERT INTO users (id, name, surname, email, password, role, status, created_at, updated_at)
     VALUES (1, 'Mucahit', 'Yenen', 'mucahit@example.com', 'hash', 1, 'active', {$timestamp}, {$timestamp})"
);

$user = Models::get('users')
    ->where('id', 1)
    ->first();

assertTrue(is_object($user), 'Guncellenecek kullanici modeli bulunmali.');

$user->surname = '';
$user->status = 'passive';
$result = $user->update();

assertTrue(! (bool) ($result->error ?? true), 'Update islemi basarili olmali.');

$row = $pdo->query("SELECT surname, status FROM users WHERE id = 1")->fetch(PDO::FETCH_ASSOC);

assertSame('', (string) ($row['surname'] ?? ''), 'Bos string alanlar update ile DB\'ye yazilabilmeli.');
assertSame('passive', (string) ($row['status'] ?? ''), 'Dolu alanlar update ile guncellenmeli.');

$partial = Models::get('users');
$partial->id = 1;
$partial->name = 'Yeni Ad';
$partialResult = $partial->update();

assertTrue(! (bool) ($partialResult->error ?? true), 'Kismi update islemi basarili olmali.');

$partialRow = $pdo->query("SELECT name, surname, status FROM users WHERE id = 1")->fetch(PDO::FETCH_ASSOC);

assertSame('Yeni Ad', (string) ($partialRow['name'] ?? ''), 'Sadece set edilen alan guncellenmeli.');
assertSame('', (string) ($partialRow['surname'] ?? ''), 'Set edilmeyen alanlar mevcut degerini korumali.');
assertSame('passive', (string) ($partialRow['status'] ?? ''), 'Set edilmeyen baska alanlar da korunmali.');

echo "ModelsUpdateTest ok\n";
