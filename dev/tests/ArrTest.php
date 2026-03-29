<?php

declare(strict_types=1);

use Core\Arr;

require_once __DIR__ . '/bootstrap.php';

$data = [
    'user' => [
        'name' => 'Miu',
        'profile' => [
            'city' => 'Istanbul',
        ],
    ],
    'status' => 'active',
];

assertSame('Istanbul', Arr::get($data, 'user.profile.city'), 'Noktalı anahtar okunabilmeli.');
assertTrue(Arr::has($data, 'user.name'), 'Var olan anahtar bulunabilmeli.');
assertSame(['a' => 1, 'b' => 2], Arr::merge(['a' => 1], ['b' => 2]), 'merge dizileri birlestirmeli.');
assertSame(['user' => ['roles' => ['admin', 'editor']]], Arr::mergeRecursive(['user' => ['roles' => ['admin']]], ['user' => ['roles' => ['editor']]]), 'mergeRecursive ic ice birlesim yapmali.');
assertSame(['status' => 'passive'], Arr::replace(['status' => 'active'], ['status' => 'passive']), 'replace sonraki degeri oncekinin ustune yazmali.');
assertSame(['user' => ['profile' => ['city' => 'Ankara']]], Arr::set([], 'user.profile.city', 'Ankara'), 'set noktali anahtarla deger yazabilmeli.');
assertSame(['user' => ['name' => 'MiU']], Arr::forget(['user' => ['name' => 'MiU', 'role' => 'admin']], 'user.role'), 'forget noktali anahtari silebilmeli.');
[$pulledValue, $remaining] = Arr::pull(['status' => 'active', 'role' => 'admin'], 'role');
assertSame('admin', $pulledValue, 'pull silinen degeri dondurmeli.');
assertSame(['status' => 'active'], $remaining, 'pull degeri diziden de cikarmali.');
assertTrue(Arr::contains(['user' => ['city' => 'Istanbul']], 'Istanbul'), 'contains nested deger bulabilmeli.');
assertTrue(Arr::containsKey(['user' => ['profile' => ['city' => 'Istanbul']]], 'user.profile.city'), 'containsKey nested anahtar bulabilmeli.');
assertSame('user.profile.city', Arr::search(['user' => ['profile' => ['city' => 'Istanbul']]], 'Istanbul'), 'search bulunan degerin yolunu dondurmeli.');
$found = Arr::findWhere([
    ['user' => ['id' => 4, 'name' => 'A']],
    ['user' => ['id' => 7, 'name' => 'B']],
], 'user.id', 7);
assertSame('B', $found['user']['name'] ?? null, 'findWhere nested alana gore ilk kaydi dondurmeli.');
assertSame(['value'], Arr::wrap('value'), 'wrap scalar degeri diziye cevirmeli.');
assertSame(['status' => 'active'], Arr::only($data, ['status']), 'only secilen anahtarlari dondurmeli.');
assertSame('active', Arr::first(['active', 'passive']), 'first ilk elemani dondurmeli.');
assertSame('passive', Arr::last(['active', 'passive']), 'last son elemani dondurmeli.');

$sorted = Arr::sortByKey([
    ['name' => 'Beta', 'order' => 2],
    ['name' => 'Alpha', 'order' => 1],
], 'order');

assertSame('Alpha', $sorted[0]['name'] ?? null, 'sortByKey artan siralama yapmali.');

$mapped = Arr::map([1, 2, 3], static fn (int $value): int => $value * 2);
assertSame([2, 4, 6], array_values($mapped), 'map elemanlari donusturmeli.');

$filtered = Arr::filter([1, 2, 3, 4], static fn (int $value): bool => $value % 2 === 0);
assertSame([1 => 2, 3 => 4], $filtered, 'filter kosulu saglayanlari dondurmeli.');

$reduced = Arr::reduce([1, 2, 3], static fn (int $carry, int $value): int => $carry + $value, 0);
assertSame(6, $reduced, 'reduce tum elemanlari birlestirmeli.');

$grouped = Arr::groupBy([
    ['type' => 'admin', 'name' => 'A'],
    ['type' => 'user', 'name' => 'B'],
    ['type' => 'admin', 'name' => 'C'],
], 'type');
assertSame(2, count($grouped['admin'] ?? []), 'groupBy ayni anahtarlari gruplayabilmeli.');

$keyed = Arr::keyBy([
    ['id' => 10, 'name' => 'Test'],
    ['id' => 11, 'name' => 'Demo'],
], 'id');
assertSame('Demo', $keyed[11]['name'] ?? null, 'keyBy kayitlari verilen anahtarla indexlemeli.');

$flattened = Arr::flatten([1, [2, [3, 4]]]);
assertSame([1, 2, 3, 4], $flattened, 'flatten ic ice dizileri tek diziye cevirmeli.');

$where = Arr::where([
    ['status' => 'active', 'name' => 'A'],
    ['status' => 'passive', 'name' => 'B'],
], 'status', 'active');
assertSame('A', $where[0]['name'] ?? null, 'where belirli degerle filtreleme yapmali.');

$sortedBy = Arr::sortBy([
    ['name' => 'Beta'],
    ['name' => 'Alpha'],
], static fn (array $item): string => $item['name']);
assertSame('Alpha', Arr::first($sortedBy)['name'] ?? null, 'sortBy callback ile siralayabilmeli.');

$sortedKeys = Arr::sortKeys([
    'b' => 2,
    'a' => 1,
]);

assertSame(['a' => 1, 'b' => 2], $sortedKeys, 'sortKeys anahtara gore siralamali.');
assertSame([1, 2], Arr::values(['x' => 1, 'y' => 2]), 'values degerleri yeniden indexlemeli.');
assertTrue(Arr::isAssoc(['x' => 1, 'y' => 2]), 'isAssoc associative diziyi tanimali.');

echo "ArrTest ok\n";
