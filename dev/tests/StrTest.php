<?php

declare(strict_types=1);

use Core\Str;

require_once __DIR__ . '/bootstrap.php';

assertTrue(Str::contains('MiU Framework', 'Framework'), 'contains metin icinde arama yapmali.');
assertTrue(Str::startsWith('MiU Framework', 'MiU'), 'startsWith baslangici kontrol etmeli.');
assertTrue(Str::endsWith('MiU Framework', 'Framework'), 'endsWith bitisi kontrol etmeli.');
assertSame('MiU', Str::before('MiU Framework', ' Framework'), 'before aranan bolumden oncekini dondurmeli.');
assertSame('Framework', Str::after('MiU Framework', 'MiU '), 'after aranan bolumden sonrakini dondurmeli.');
assertSame('Framework', Str::between('[Framework]', '[', ']'), 'between iki isaret arasini dondurmeli.');
assertSame('miu framework', Str::lower('MiU Framework'), 'lower kucuk harfe cevirmeli.');
assertSame(13, Str::length('MiU Framework'), 'length karakter sayisini dondurmeli.');
assertSame('MIU FRAMEWORK', Str::upper('MiU Framework'), 'upper buyuk harfe cevirmeli.');
assertSame('Miu Framework', Str::title('miu framework'), 'title kelime baslarini buyutmeli.');
assertSame('MiU Framework', Str::squish("  MiU \n Framework  "), 'squish fazla bosluklari temizlemeli.');
assertSame('MiU F...', Str::limit('MiU Framework', 5), 'limit uzun metni kisaltmali.');
assertSame('MiU...', Str::shorten('MiU Framework Core', 6), 'shorten kelimeyi ortadan bolmeden kisaltmali.');
assertSame('miu-framework', Str::slug('MiU Framework'), 'slug url dostu metin uretmeli.');
assertSame('mi_u_framework', Str::snake('MiUFramework'), 'snake snake_case uretmeli.');
assertSame('mi-u-framework', Str::kebab('MiUFramework'), 'kebab kebab-case uretmeli.');
assertSame('MiuFramework', Str::studly('miu_framework'), 'studly StudlyCase uretmeli.');
assertSame('miuFramework', Str::camel('miu_framework'), 'camel camelCase uretmeli.');
assertSame('MiU Core', Str::replace('Framework', 'Core', 'MiU Framework'), 'replace metin degistirmeli.');
assertSame('Merhaba MiU', Str::translate('Hello Framework', [
    'Hello' => 'Merhaba',
    'Framework' => 'MiU',
]), 'translate anahtar-deger eslesmesiyle donusum yapmali.');

echo "StrTest ok\n";
