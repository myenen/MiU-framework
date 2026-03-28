<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__, 2);
$stubRoot = $projectRoot . '/dev/stubs';

$command = $argv[1] ?? '';
$name = $argv[2] ?? '';

if ($command === '' || in_array($command, ['-h', '--help', 'help'], true)) {
    printHelp();
    exit(0);
}

if ($name === '') {
    fwrite(STDERR, "Sinif veya sayfa adi vermelisiniz.\n");
    printHelp();
    exit(1);
}

$className = normalizeClassName($name);
$slug = toKebabCase($className);
$title = toTitle($className);

$commands = [
    'site-page' => static function () use ($projectRoot, $stubRoot, $className, $slug, $title): array {
        $results = [
            createFileFromStub($stubRoot . '/site-controller.stub', $projectRoot . '/app/Controllers/Site/' . $className . '.php', [
                '{{class_name}}' => $className,
                '{{view_name}}' => $slug,
                '{{title}}' => $title,
            ]),
            createFileFromStub($stubRoot . '/site-page-service.stub', $projectRoot . '/app/Services/Site/' . $className . 'PageService.php', [
                '{{class_name}}' => $className,
                '{{title}}' => $title,
            ]),
            createFileFromStub($stubRoot . '/site-view.stub', $projectRoot . '/resources/views/site/pages/' . $slug . '.html', [
                '{{title}}' => $title,
                '{{body}}' => $title . ' sayfasi hazir. Icerigi duzenleyebilirsiniz.',
            ]),
        ];

        $results[] = ensureRouteRegistered(
            $projectRoot . '/config/routes.php',
            'App\\Controllers\\Site\\' . $className,
            null,
            "['GET', '/" . $slug . "', [" . $className . "::class, 'index']],"
        );

        return $results;
    },
    'admin-page' => static function () use ($projectRoot, $stubRoot, $className, $slug, $title): array {
        $results = [
            createFileFromStub($stubRoot . '/admin-controller.stub', $projectRoot . '/app/Controllers/Admin/' . $className . '.php', [
                '{{class_name}}' => $className,
                '{{view_path}}' => $slug,
                '{{title}}' => $title,
            ]),
            createFileFromStub($stubRoot . '/admin-page-service.stub', $projectRoot . '/app/Services/Admin/' . $className . 'PageService.php', [
                '{{class_name}}' => $className,
                '{{title}}' => $title,
            ]),
            createFileFromStub($stubRoot . '/admin-view.stub', $projectRoot . '/resources/views/admin/pages/' . $slug . '.html', [
                '{{title}}' => $title,
                '{{body}}' => $title . ' admin sayfasi hazir. Icerigi duzenleyebilirsiniz.',
            ]),
        ];

        $results[] = ensureRouteRegistered(
            $projectRoot . '/config/routes.php',
            'App\\Controllers\\Admin\\' . $className,
            null,
            "['GET', '/admin/" . $slug . "', [" . $className . "::class, 'index']],"
        );

        return $results;
    },
    'api-endpoint' => static function () use ($projectRoot, $stubRoot, $className): array {
        $results = [
            createFileFromStub($stubRoot . '/api-controller.stub', $projectRoot . '/app/Controllers/Api/' . $className . '.php', [
                '{{class_name}}' => $className,
            ]),
            createFileFromStub($stubRoot . '/api-service.stub', $projectRoot . '/app/Services/Api/' . $className . 'Service.php', [
                '{{class_name}}' => $className,
            ]),
        ];

        $results[] = ensureRouteRegistered(
            $projectRoot . '/config/routes.php',
            'App\\Controllers\\Api\\' . $className,
            null,
            "['GET', '/api/v1/" . $slug . "', [" . $className . "::class, 'index']],"
        );

        return $results;
    },
    'service' => static function () use ($projectRoot, $stubRoot, $className): array {
        return [
            createFileFromStub($stubRoot . '/api-service.stub', $projectRoot . '/app/Services/' . $className . '.php', [
                '{{class_name}}' => $className,
            ], [
                'namespace App\\Services\\Api;' => 'namespace App\\Services;',
                'final class ' . $className . 'Service' => 'final class ' . $className,
                '* {{class_name}} API is mantigini yonetir.' => '* ' . $className . ' servis mantigini yonetir.',
                "'{{class_name}} API sonucu hazir.'" => "'" . $className . " sonucu hazir.'",
            ]),
        ];
    },
    'request' => static function () use ($projectRoot, $stubRoot, $className): array {
        return [
            createFileFromStub($stubRoot . '/request.stub', $projectRoot . '/app/Requests/' . $className . '.php', [
                '{{namespace}}' => 'App\\Requests',
                '{{class_name}}' => $className,
            ]),
        ];
    },
];

if (! isset($commands[$command])) {
    fwrite(STDERR, "Bilinmeyen komut: {$command}\n");
    printHelp();
    exit(1);
}

$results = $commands[$command]();

foreach ($results as $result) {
    fwrite(STDOUT, $result . PHP_EOL);
}

exit(0);

/**
 * Kullanim bilgisini yazar.
 *
 * @return void
 */
function printHelp(): void
{
    fwrite(STDOUT, <<<TEXT
Kullanim:
  php dev/bin/make.php site-page OrnekSayfa
  php dev/bin/make.php admin-page Kullanicilar
  php dev/bin/make.php api-endpoint Orders
  php dev/bin/make.php service SmsService
  php dev/bin/make.php request ContactFormRequest

TEXT);
}

/**
 * Stub dosyasini okuyup hedef dosyayi olusturur.
 *
 * @param string $stubPath Stub dosya yolu.
 * @param string $targetPath Hedef dosya yolu.
 * @param array<string, string> $replacements Stub icindeki yer degistirmeler.
 * @param array<string, string> $afterReplacements Ek sonradan degistirmeler.
 * @return string
 */
function createFileFromStub(string $stubPath, string $targetPath, array $replacements, array $afterReplacements = []): string
{
    if (file_exists($targetPath)) {
        return 'Atlandi: ' . $targetPath . ' zaten var.';
    }

    $content = file_get_contents($stubPath);

    if (! is_string($content)) {
        throw new RuntimeException('Stub okunamadi: ' . $stubPath);
    }

    $content = strtr($content, $replacements);

    if ($afterReplacements !== []) {
        $content = strtr($content, $afterReplacements);
    }

    $directory = dirname($targetPath);

    if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
        throw new RuntimeException('Klasor olusturulamadi: ' . $directory);
    }

    file_put_contents($targetPath, $content);

    return 'Olusturuldu: ' . $targetPath;
}

/**
 * Verilen controller icin gerekli use satirini ve route kaydini ekler.
 *
 * @param string $routesPath Route dosya yolu.
 * @param string $controllerClass Tam nitelikli controller sinifi.
 * @param string|null $alias Opsiyonel use alias degeri.
 * @param string $routeLine Eklenecek route satiri.
 * @return string
 */
function ensureRouteRegistered(string $routesPath, string $controllerClass, ?string $alias, string $routeLine): string
{
    $content = file_get_contents($routesPath);

    if (! is_string($content)) {
        throw new RuntimeException('Route dosyasi okunamadi: ' . $routesPath);
    }

    $importLine = buildImportLine($controllerClass, $alias);

    if (! str_contains($content, $importLine)) {
        $content = insertUseStatement($content, $importLine);
    }

    if (str_contains($content, $routeLine)) {
        file_put_contents($routesPath, $content);

        return 'Atlandi: route zaten var.';
    }

    $marker = "\n];";
    $insert = '    ' . $routeLine . "\n";

    if (! str_contains($content, $marker)) {
        throw new RuntimeException('Route dosyasi beklenen formatta degil: ' . $routesPath);
    }

    $content = str_replace($marker, $insert . '];', $content);
    file_put_contents($routesPath, $content);

    return 'Route eklendi: ' . trim($routeLine);
}

/**
 * Use satirini olusturur.
 *
 * @param string $controllerClass Tam nitelikli sinif adi.
 * @param string|null $alias Opsiyonel alias.
 * @return string
 */
function buildImportLine(string $controllerClass, ?string $alias = null): string
{
    return $alias !== null && $alias !== ''
        ? 'use ' . $controllerClass . ' as ' . $alias . ';'
        : 'use ' . $controllerClass . ';';
}

/**
 * Route dosyasindaki use bloklarina yeni bir import ekler.
 *
 * @param string $content Route dosyasi icerigi.
 * @param string $importLine Eklenecek use satiri.
 * @return string
 */
function insertUseStatement(string $content, string $importLine): string
{
    if (preg_match_all('/^use\s.+;$/m', $content, $matches, PREG_OFFSET_CAPTURE) > 0) {
        $lastMatch = $matches[0][count($matches[0]) - 1];
        $insertPos = $lastMatch[1] + strlen($lastMatch[0]);

        return substr($content, 0, $insertPos) . "\n" . $importLine . substr($content, $insertPos);
    }

    $marker = "declare(strict_types=1);\n";

    if (! str_contains($content, $marker)) {
        return $importLine . "\n" . $content;
    }

    return str_replace($marker, $marker . "\n" . $importLine . "\n", $content);
}

/**
 * Girilen adi uygun PHP class adina cevirir.
 *
 * @param string $name Ham giris.
 * @return string
 */
function normalizeClassName(string $name): string
{
    $name = preg_replace('/([a-z0-9])([A-Z])/', '$1 $2', $name) ?? $name;
    $name = preg_replace('/[^A-Za-z0-9]+/', ' ', $name) ?? $name;
    $parts = array_filter(explode(' ', trim($name)));
    $parts = array_map(static fn (string $part): string => ucfirst(strtolower($part)), $parts);

    return implode('', $parts);
}

/**
 * Class adini kebab-case gorunumune cevirir.
 *
 * @param string $value Class adi.
 * @return string
 */
function toKebabCase(string $value): string
{
    $value = preg_replace('/([a-z])([A-Z])/', '$1-$2', $value) ?? $value;

    return strtolower($value);
}

/**
 * Class adini insan okunur basliga cevirir.
 *
 * @param string $value Class adi.
 * @return string
 */
function toTitle(string $value): string
{
    $value = preg_replace('/([a-z])([A-Z])/', '$1 $2', $value) ?? $value;

    return trim($value);
}
