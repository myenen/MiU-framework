<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__, 2);
$outputDirectory = $projectRoot . '/dev/docs';
$outputFile = $outputDirectory . '/api-reference.md';

require_once $projectRoot . '/vendor/autoload.php';
require_once $projectRoot . '/config/bootstrap.php';
$classes = collectProjectClasses($projectRoot);
$functions = collectProjectFunctions($projectRoot);

if (! is_dir($outputDirectory)) {
    mkdir($outputDirectory, 0777, true);
}

file_put_contents($outputFile, buildMarkdown($classes, $functions, $projectRoot));

echo "Dokumantasyon olusturuldu: {$outputFile}" . PHP_EOL;

/**
 * Proje icindeki siniflari toplar.
 *
 * @param string $projectRoot Proje kok dizini.
 * @return array<int, ReflectionClass<object>>
 */
function collectProjectClasses(string $projectRoot): array
{
    $classes = [];
    $classNames = discoverProjectClassNames($projectRoot);

    foreach ($classNames as $className) {
        if (! class_exists($className) && ! interface_exists($className) && ! trait_exists($className)) {
            continue;
        }

        $reflection = new ReflectionClass($className);
        $fileName = $reflection->getFileName();

        if (! is_string($fileName) || ! str_starts_with($fileName, $projectRoot)) {
            continue;
        }

        if (str_contains($fileName, '/vendor/')) {
            continue;
        }

        $classes[] = $reflection;
    }

    usort(
        $classes,
        static fn (ReflectionClass $left, ReflectionClass $right): int => strcmp($left->getName(), $right->getName())
    );

    return $classes;
}

/**
 * Proje dosyalarindan namespace ve sinif adlarini bulur.
 *
 * @param string $projectRoot Proje kok dizini.
 * @return array<int, string>
 */
function discoverProjectClassNames(string $projectRoot): array
{
    $directories = [
        $projectRoot . '/core',
        $projectRoot . '/app',
    ];
    $classNames = [];

    foreach ($directories as $directory) {
        if (! is_dir($directory)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $className = extractClassNameFromFile($file->getPathname());
            if ($className !== null) {
                $classNames[] = $className;
            }
        }
    }

    sort($classNames);

    return array_values(array_unique($classNames));
}

/**
 * Bir PHP dosyasindan tam nitelikli sinif adini cikarir.
 *
 * @param string $filePath PHP dosya yolu.
 * @return string|null
 */
function extractClassNameFromFile(string $filePath): ?string
{
    $code = file_get_contents($filePath);
    if (! is_string($code)) {
        return null;
    }

    $tokens = token_get_all($code);
    $namespace = '';
    $className = null;
    $tokenCount = count($tokens);

    for ($index = 0; $index < $tokenCount; $index++) {
        $token = $tokens[$index];

        if (! is_array($token)) {
            continue;
        }

        if ($token[0] === T_NAMESPACE) {
            $namespace = readNamespaceTokens($tokens, $index + 1);
            continue;
        }

        if (in_array($token[0], [T_CLASS, T_INTERFACE, T_TRAIT], true)) {
            $className = readNextStringToken($tokens, $index + 1);
            if ($className !== null) {
                break;
            }
        }
    }

    if ($className === null) {
        return null;
    }

    return $namespace !== '' ? $namespace . '\\' . $className : $className;
}

/**
 * Token listesinden namespace metnini okur.
 *
 * @param array<int, mixed> $tokens PHP token listesi.
 * @param int $startIndex Baslangic indeksi.
 * @return string
 */
function readNamespaceTokens(array $tokens, int $startIndex): string
{
    $parts = [];
    $tokenCount = count($tokens);

    for ($index = $startIndex; $index < $tokenCount; $index++) {
        $token = $tokens[$index];

        if (is_string($token) && ($token === ';' || $token === '{')) {
            break;
        }

        if (! is_array($token)) {
            continue;
        }

        if (in_array($token[0], [T_STRING, T_NAME_QUALIFIED, T_NS_SEPARATOR], true)) {
            $parts[] = $token[1];
        }
    }

    return trim(implode('', $parts), '\\');
}

/**
 * Token listesindeki bir sonraki anlamli isim degerini bulur.
 *
 * @param array<int, mixed> $tokens PHP token listesi.
 * @param int $startIndex Baslangic indeksi.
 * @return string|null
 */
function readNextStringToken(array $tokens, int $startIndex): ?string
{
    $tokenCount = count($tokens);

    for ($index = $startIndex; $index < $tokenCount; $index++) {
        $token = $tokens[$index];

        if (! is_array($token)) {
            if ($token === '{' || $token === '(') {
                return null;
            }

            continue;
        }

        if ($token[0] === T_STRING) {
            return $token[1];
        }
    }

    return null;
}

/**
 * Proje icindeki global fonksiyonlari toplar.
 *
 * @param string $projectRoot Proje kok dizini.
 * @return array<int, ReflectionFunction>
 */
function collectProjectFunctions(string $projectRoot): array
{
    $functions = [];
    $defined = get_defined_functions();

    foreach ($defined['user'] as $functionName) {
        $reflection = new ReflectionFunction($functionName);
        $fileName = $reflection->getFileName();

        if (! is_string($fileName) || ! str_starts_with($fileName, $projectRoot)) {
            continue;
        }

        if (str_contains($fileName, '/vendor/') || str_contains($fileName, '/dev/bin/')) {
            continue;
        }

        $functions[] = $reflection;
    }

    usort(
        $functions,
        static fn (ReflectionFunction $left, ReflectionFunction $right): int => strcmp($left->getName(), $right->getName())
    );

    return $functions;
}

/**
 * Sinif ve fonksiyon bilgilerini Markdown dokumanina donusturur.
 *
 * @param array<int, ReflectionClass<object>> $classes Yansitilan sinif listesi.
 * @param array<int, ReflectionFunction> $functions Yansitilan fonksiyon listesi.
 * @param string $projectRoot Proje kok dizini.
 * @return string
 */
function buildMarkdown(array $classes, array $functions, string $projectRoot): string
{
    $lines = [];
    $lines[] = '# API Referansi';
    $lines[] = '';
    $lines[] = 'Bu dosya `dev/bin/generate-docs.php` tarafindan otomatik uretilir.';
    $lines[] = '';
    $lines[] = '## Icerik';
    $lines[] = '';

    if ($functions !== []) {
        $lines[] = '- [Global Fonksiyonlar](#global-fonksiyonlar)';
    }

    foreach ($classes as $class) {
        $lines[] = '- [' . $class->getName() . '](#' . slugify($class->getName()) . ')';
    }

    $lines[] = '';

    if ($functions !== []) {
        $lines[] = '## Global Fonksiyonlar';
        $lines[] = '';

        foreach ($functions as $function) {
            $filePath = relativePath($function->getFileName() ?: '', $projectRoot);
            if (str_starts_with($filePath, 'dev/bin/')) {
                continue;
            }

            $lines = array_merge($lines, renderFunctionBlock($function, $projectRoot, 3));
        }
    }

    foreach ($classes as $class) {
        $lines[] = '## ' . $class->getName();
        $lines[] = '';
        $lines[] = '- Dosya: `' . relativePath($class->getFileName() ?: '', $projectRoot) . '`';

        $summary = extractSummary($class->getDocComment() ?: '');
        if ($summary !== '') {
            $lines[] = '- Aciklama: ' . $summary;
        }

        $methods = array_filter(
            $class->getMethods(),
            static fn (ReflectionMethod $method): bool => $method->getDeclaringClass()->getName() === $class->getName()
        );

        usort(
            $methods,
            static fn (ReflectionMethod $left, ReflectionMethod $right): int => strcmp($left->getName(), $right->getName())
        );

        $lines[] = '- Metod sayisi: ' . (string) count($methods);
        $lines[] = '';

        foreach ($methods as $method) {
            $lines = array_merge($lines, renderMethodBlock($method));
        }
    }

    return implode(PHP_EOL, $lines) . PHP_EOL;
}

/**
 * Bir global fonksiyon bolumunu olusturur.
 *
 * @param ReflectionFunction $function Fonksiyon yansitmasi.
 * @param string $projectRoot Proje kok dizini.
 * @param int $headingLevel Baslik seviyesi.
 * @return array<int, string>
 */
function renderFunctionBlock(ReflectionFunction $function, string $projectRoot, int $headingLevel = 3): array
{
    $lines = [];
    $lines[] = str_repeat('#', $headingLevel) . ' ' . $function->getName();
    $lines[] = '';
    $lines[] = '- Dosya: `' . relativePath($function->getFileName() ?: '', $projectRoot) . '`';

    $summary = extractSummary($function->getDocComment() ?: '');
    if ($summary !== '') {
        $lines[] = '- Aciklama: ' . $summary;
    }

    $signature = buildFunctionSignature($function);
    $lines[] = '- Imza: `' . $signature . '`';

    $doc = parseDocblock($function->getDocComment() ?: '');
    $returnInfo = $doc['return'][0] ?? '';
    if ($returnInfo !== '') {
        $lines[] = '- Donus: ' . $returnInfo;
    }

    if ($doc['params'] !== []) {
        $lines[] = '- Parametreler:';
        foreach ($doc['params'] as $param) {
            $lines[] = '  - `' . $param['name'] . '` (' . $param['type'] . '): ' . $param['description'];
        }
    }

    $lines[] = '';

    return $lines;
}

/**
 * Bir metod bolumunu olusturur.
 *
 * @param ReflectionMethod $method Metod yansitmasi.
 * @return array<int, string>
 */
function renderMethodBlock(ReflectionMethod $method): array
{
    $lines = [];
    $lines[] = '### ' . $method->getName();
    $lines[] = '';

    $visibility = $method->isPublic() ? 'public' : ($method->isProtected() ? 'protected' : 'private');
    $lines[] = '- Erisim: `' . $visibility . '`';
    $lines[] = '- Imza: `' . buildMethodSignature($method) . '`';

    $summary = extractSummary($method->getDocComment() ?: '');
    if ($summary !== '') {
        $lines[] = '- Aciklama: ' . $summary;
    }

    $doc = parseDocblock($method->getDocComment() ?: '');
    $returnInfo = $doc['return'][0] ?? '';
    if ($returnInfo !== '') {
        $lines[] = '- Donus: ' . $returnInfo;
    }

    if ($doc['params'] !== []) {
        $lines[] = '- Parametreler:';
        foreach ($doc['params'] as $param) {
            $lines[] = '  - `' . $param['name'] . '` (' . $param['type'] . '): ' . $param['description'];
        }
    }

    $lines[] = '';

    return $lines;
}

/**
 * Docblock ozet metnini cikarir.
 *
 * @param string $docblock Ham docblock metni.
 * @return string
 */
function extractSummary(string $docblock): string
{
    $parsed = parseDocblock($docblock);

    return trim($parsed['summary'], " /");
}

/**
 * Docblock icerigini ozet, parametre ve donus alanlarina ayirir.
 *
 * @param string $docblock Ham docblock metni.
 * @return array{summary: string, params: array<int, array{name: string, type: string, description: string}>, return: array<int, string>}
 */
function parseDocblock(string $docblock): array
{
    if ($docblock === '') {
        return [
            'summary' => '',
            'params' => [],
            'return' => [],
        ];
    }

    $lines = preg_split('/\R/', $docblock) ?: [];
    $summaryLines = [];
    $params = [];
    $returns = [];

    foreach ($lines as $line) {
        $clean = trim($line);
        $clean = preg_replace('/^\/\*\*?/', '', $clean) ?? $clean;
        $clean = preg_replace('/^\* ?/', '', $clean) ?? $clean;
        $clean = preg_replace('/\*\/$/', '', $clean) ?? $clean;
        $clean = trim($clean);

        if ($clean === '' || $clean === '/') {
            continue;
        }

        if (str_starts_with($clean, '@param ')) {
            if (preg_match('/^@param\s+(\S+)\s+(\$\S+)\s*(.*)$/', $clean, $matches) === 1) {
                $params[] = [
                    'type' => $matches[1],
                    'name' => $matches[2],
                    'description' => trim($matches[3]),
                ];
            }

            continue;
        }

        if (str_starts_with($clean, '@return ')) {
            $returns[] = trim(substr($clean, 8));
            continue;
        }

        if (! str_starts_with($clean, '@')) {
            $summaryLines[] = $clean;
        }
    }

    return [
        'summary' => implode(' ', $summaryLines),
        'params' => $params,
        'return' => $returns,
    ];
}

/**
 * ReflectionFunction icin okunabilir imza metni olusturur.
 *
 * @param ReflectionFunction $function Fonksiyon yansitmasi.
 * @return string
 */
function buildFunctionSignature(ReflectionFunction $function): string
{
    return $function->getName() . '(' . implode(', ', array_map(renderParameter(...), $function->getParameters())) . ')';
}

/**
 * ReflectionMethod icin okunabilir imza metni olusturur.
 *
 * @param ReflectionMethod $method Metod yansitmasi.
 * @return string
 */
function buildMethodSignature(ReflectionMethod $method): string
{
    $signature = $method->getName() . '(' . implode(', ', array_map(renderParameter(...), $method->getParameters())) . ')';
    $returnType = renderType($method->getReturnType());

    if ($returnType !== '') {
        $signature .= ': ' . $returnType;
    }

    return $signature;
}

/**
 * Bir parametreyi metin olarak bicimlendirir.
 *
 * @param ReflectionParameter $parameter Parametre yansitmasi.
 * @return string
 */
function renderParameter(ReflectionParameter $parameter): string
{
    $type = renderType($parameter->getType());
    $prefix = $type !== '' ? $type . ' ' : '';
    $variadic = $parameter->isVariadic() ? '...' : '';
    $default = '';

    if ($parameter->isDefaultValueAvailable() && ! $parameter->isVariadic()) {
        $defaultValue = $parameter->getDefaultValue();
        $default = ' = ' . var_export($defaultValue, true);
    }

    return $prefix . $variadic . '$' . $parameter->getName() . $default;
}

/**
 * Reflection tip bilgisini metne donusturur.
 *
 * @param ReflectionType|null $type Tip bilgisi.
 * @return string
 */
function renderType(?ReflectionType $type): string
{
    if ($type === null) {
        return '';
    }

    if ($type instanceof ReflectionNamedType) {
        return $type->getName();
    }

    if ($type instanceof ReflectionUnionType) {
        return implode('|', array_map(static fn (ReflectionType $inner): string => renderType($inner), $type->getTypes()));
    }

    if ($type instanceof ReflectionIntersectionType) {
        return implode('&', array_map(static fn (ReflectionType $inner): string => renderType($inner), $type->getTypes()));
    }

    return '';
}

/**
 * Dosya yolunu proje kokune gore kisaltir.
 *
 * @param string $path Tam dosya yolu.
 * @param string $projectRoot Proje kok dizini.
 * @return string
 */
function relativePath(string $path, string $projectRoot): string
{
    if ($path === '') {
        return '';
    }

    return ltrim(str_replace($projectRoot, '', $path), '/');
}

/**
 * Basliklar icin basit bir anchor degeri uretir.
 *
 * @param string $value Kaynak metin.
 * @return string
 */
function slugify(string $value): string
{
    $slug = strtolower($value);
    $slug = str_replace('\\', '', $slug);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? $slug;

    return trim($slug, '-');
}
