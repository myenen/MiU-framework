<?php

declare(strict_types=1);

/**
 * Harici bir HTML template'i analiz edip frameworke uygun parcalara ayirir.
 *
 * Kullanim:
 * php dev/bin/import-template.php --input=/path/to/index.html --name=modern-admin --area=admin
 */

$options = parseArguments($argv);
$projectRoot = dirname(__DIR__, 2);
$inputPath = $options['input'] ?? '';
$themeName = normalizeSlug($options['name'] ?? '');
$area = in_array(($options['area'] ?? 'site'), ['site', 'admin'], true) ? (string) $options['area'] : 'site';
$pageName = normalizeSlug($options['page'] ?? 'index');

if ($inputPath === '' || $themeName === '') {
    fwrite(STDERR, "Kullanim: php dev/bin/import-template.php --input=/path/to/index.html --name=theme-name [--area=site|admin] [--page=index]\n");
    exit(1);
}

$resolvedInput = realpath($inputPath);

if ($resolvedInput === false) {
    fwrite(STDERR, "Girdi bulunamadi: {$inputPath}\n");
    exit(1);
}

if (is_dir($resolvedInput)) {
    $resolvedInput = findHtmlEntry($resolvedInput);
}

if ($resolvedInput === null || ! is_file($resolvedInput)) {
    fwrite(STDERR, "HTML girisi bulunamadi.\n");
    exit(1);
}

$sourceRoot = dirname($resolvedInput);
$html = file_get_contents($resolvedInput);

if (! is_string($html) || trim($html) === '') {
    fwrite(STDERR, "HTML icerigi okunamadi: {$resolvedInput}\n");
    exit(1);
}

libxml_use_internal_errors(true);
$document = new DOMDocument('1.0', 'UTF-8');
$document->preserveWhiteSpace = false;
$document->formatOutput = true;
$document->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);
libxml_clear_errors();

$xpath = new DOMXPath($document);
$copiedAssets = [];
$analysisRoot = $projectRoot . '/dev/template-breakdowns/' . $themeName;
$layoutDir = $analysisRoot . '/layouts';
$partialDir = $analysisRoot . '/partials';
$pageDir = $analysisRoot . '/pages';

ensureDirectory($layoutDir);
ensureDirectory($partialDir);
ensureDirectory($pageDir);

$body = $document->getElementsByTagName('body')->item(0);
$head = $document->getElementsByTagName('head')->item(0);

if (! $body instanceof DOMElement) {
    fwrite(STDERR, "Body etiketi bulunamadi.\n");
    exit(1);
}

$tokens = [
    'content' => '__CODEX_CONTENT__',
    'header' => '__CODEX_HEADER__',
    'sidebar' => '__CODEX_SIDEBAR__',
    'footer' => '__CODEX_FOOTER__',
];

$headerNode = findFirstStructuralNode($xpath, $body, [
    './/header',
]);

$sidebarNode = findFirstStructuralNode($xpath, $body, [
    './/aside',
    './/*[self::nav or self::div or self::section][contains(@class,"sidebar")]',
    './/*[self::nav or self::div or self::section][contains(@class,"side-bar")]',
    './/*[self::nav or self::div or self::section][contains(@class,"sidemenu")]',
]);

$footerNode = findFirstStructuralNode($xpath, $body, [
    './/footer',
]);

$mainNode = findFirstStructuralNode($xpath, $body, [
    './/main',
    './/*[self::section or self::div][contains(@class,"main-content")]',
    './/*[self::section or self::div][contains(@class,"page-content")]',
    './/*[self::section or self::div][contains(@class,"content")]',
]);

$partials = [];

if ($headerNode instanceof DOMElement) {
    $partials['header'] = trim(nodeToHtml($headerNode));
    replaceNodeWithToken($document, $headerNode, $tokens['header']);
}

if ($sidebarNode instanceof DOMElement) {
    $partials['sidebar'] = trim(nodeToHtml($sidebarNode));
    replaceNodeWithToken($document, $sidebarNode, $tokens['sidebar']);
}

if ($footerNode instanceof DOMElement) {
    $partials['footer'] = trim(nodeToHtml($footerNode));
    replaceNodeWithToken($document, $footerNode, $tokens['footer']);
}

$pageContent = '';

if ($mainNode instanceof DOMElement) {
    $pageContent = trim(innerHtml($mainNode));
    replaceNodeWithToken($document, $mainNode, $tokens['content']);
} else {
    $pageContent = trim(innerHtml($body));
    removeTokenText($pageContent, array_values($tokens));
    clearChildren($body);
    $body->appendChild($document->createTextNode($tokens['content']));
}

$assets = collectAssetReferences($document, $sourceRoot);
$headHtml = $head instanceof DOMElement ? trim(innerHtml($head)) : buildFallbackHead($area);
$bodyHtml = trim(innerHtml($body));

$layoutHtml = buildLayoutHtml($document, $headHtml, $bodyHtml, $themeName, $partials, $tokens);
$pageHtml = $pageContent !== '' ? $pageContent : '<div>{{Icerik buraya gelecek}}</div>';

file_put_contents($layoutDir . '/' . $themeName . '.html', $layoutHtml);
file_put_contents($pageDir . '/' . $themeName . '-' . $pageName . '.html', $pageHtml . PHP_EOL);

foreach ($partials as $name => $content) {
    file_put_contents($partialDir . '/' . $name . '.html', $content . PHP_EOL);
}

file_put_contents($analysisRoot . '/assets.json', json_encode($assets, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);
file_put_contents($analysisRoot . '/summary.json', json_encode([
    'source' => $resolvedInput,
    'area' => $area,
    'theme' => $themeName,
    'page' => $pageName,
    'partials' => array_keys($partials),
    'assets_count' => count($assets),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);

fwrite(STDOUT, "Template import tamamlandi.\n");
fwrite(STDOUT, "Layout: {$layoutDir}/{$themeName}.html\n");
fwrite(STDOUT, "Page: {$pageDir}/{$themeName}-{$pageName}.html\n");

foreach (array_keys($partials) as $name) {
    fwrite(STDOUT, "Partial: {$partialDir}/{$name}.html\n");
}

fwrite(STDOUT, "Asset listesi: {$analysisRoot}/assets.json\n");
fwrite(STDOUT, "Ozet: {$analysisRoot}/summary.json\n");

/**
 * @param array<int, string> $argv
 * @return array<string, string>
 */
function parseArguments(array $argv): array
{
    $options = [];

    foreach (array_slice($argv, 1) as $argument) {
        if (! str_starts_with($argument, '--')) {
            continue;
        }

        $parts = explode('=', substr($argument, 2), 2);
        $options[$parts[0]] = $parts[1] ?? '1';
    }

    return $options;
}

function findHtmlEntry(string $directory): ?string
{
    $preferred = ['index.html', 'index.htm'];

    foreach ($preferred as $file) {
        $path = $directory . '/' . $file;

        if (is_file($path)) {
            return $path;
        }
    }

    $matches = glob($directory . '/*.html') ?: [];

    return $matches[0] ?? null;
}

function normalizeSlug(string $value): string
{
    $value = trim($value);
    $value = preg_replace('/[^A-Za-z0-9]+/', '-', $value) ?? $value;
    $value = trim((string) $value, '-');

    return strtolower($value);
}

function ensureDirectory(string $path): void
{
    if (! is_dir($path) && ! mkdir($path, 0777, true) && ! is_dir($path)) {
        throw new RuntimeException('Klasor olusturulamadi: ' . $path);
    }
}

/**
 * @param array<int, string> $queries
 */
function findFirstStructuralNode(DOMXPath $xpath, DOMElement $context, array $queries): ?DOMElement
{
    foreach ($queries as $query) {
        $nodes = $xpath->query($query, $context);

        if (! $nodes instanceof DOMNodeList) {
            continue;
        }

        foreach ($nodes as $node) {
            if ($node instanceof DOMElement) {
                return $node;
            }
        }
    }

    return null;
}

/**
 */
function collectAssetReferences(DOMDocument $document, string $sourceRoot): array
{
    $xpath = new DOMXPath($document);
    $nodes = $xpath->query('//*[@href or @src]');
    $assets = [];

    if (! $nodes instanceof DOMNodeList) {
        return [];
    }

    foreach ($nodes as $node) {
        if (! $node instanceof DOMElement) {
            continue;
        }

        foreach (['href', 'src'] as $attribute) {
            if (! $node->hasAttribute($attribute)) {
                continue;
            }

            $value = trim($node->getAttribute($attribute));
            $asset = describeAssetReference($value, $sourceRoot, $attribute);

            if ($asset !== null) {
                $assets[] = $asset;
            }
        }
    }

    return $assets;
}

/**
 * @return array<string, mixed>|null
 */
function describeAssetReference(string $value, string $sourceRoot, string $attribute): ?array
{
    if ($value === '' || str_starts_with($value, '#')) {
        return null;
    }

    if (preg_match('/^(https?:)?\/\//i', $value) === 1 || str_starts_with($value, 'data:') || str_starts_with($value, 'mailto:') || str_starts_with($value, 'javascript:')) {
        return [
            'attribute' => $attribute,
            'original' => $value,
            'type' => 'external',
            'resolved' => null,
            'exists' => false,
        ];
    }

    $cleanValue = strtok($value, '#') ?: $value;
    $cleanValue = strtok($cleanValue, '?') ?: $cleanValue;

    if ($cleanValue === '') {
        return null;
    }

    $relative = ltrim(str_replace('\\', '/', $cleanValue), '/');
    $relative = preg_replace('#(^|/)\./#', '$1', $relative) ?? $relative;
    $absolute = str_starts_with($cleanValue, '/')
        ? realpath($sourceRoot . $cleanValue)
        : realpath($sourceRoot . '/' . $relative);

    return [
        'attribute' => $attribute,
        'original' => $value,
        'type' => 'local',
        'relative' => $relative,
        'resolved' => $absolute !== false ? $absolute : null,
        'exists' => $absolute !== false && is_file($absolute),
    ];
}

function replaceNodeWithToken(DOMDocument $document, DOMElement $node, string $token): void
{
    $placeholder = $document->createTextNode($token);
    $node->parentNode?->replaceChild($placeholder, $node);
}

function nodeToHtml(DOMNode $node): string
{
    $document = $node->ownerDocument;

    return $document instanceof DOMDocument ? (string) $document->saveHTML($node) : '';
}

function innerHtml(DOMElement $element): string
{
    $html = '';

    foreach ($element->childNodes as $child) {
        $html .= nodeToHtml($child);
    }

    return $html;
}

/**
 * @param array<string, string> $partials
 * @param array<string, string> $tokens
 */
function buildLayoutHtml(DOMDocument $document, string $headHtml, string $bodyHtml, string $themeName, array $partials, array $tokens): string
{
    $bodyAttributes = '';
    $body = $document->getElementsByTagName('body')->item(0);

    if ($body instanceof DOMElement) {
        foreach ($body->attributes as $attribute) {
            $bodyAttributes .= ' ' . $attribute->nodeName . '="' . htmlspecialchars((string) $attribute->nodeValue, ENT_QUOTES, 'UTF-8') . '"';
        }
    }

    $replacements = [
        $tokens['content'] => '{content}',
        $tokens['header'] => isset($partials['header']) ? '{>partials/header}' : '',
        $tokens['sidebar'] => isset($partials['sidebar']) ? '{>partials/sidebar}' : '',
        $tokens['footer'] => isset($partials['footer']) ? '{>partials/footer}' : '',
    ];

    $bodyHtml = strtr($bodyHtml, $replacements);

    $html = "<!DOCTYPE html>\n<html lang=\"{current_locale}\">\n<head>\n" . trim($headHtml) . "\n</head>\n<body{$bodyAttributes}>\n" . trim($bodyHtml) . "\n</body>\n</html>\n";

    return $html;
}

function buildFallbackHead(string $area): string
{
    $title = $area === 'admin' ? '{admin_panel_title}' : '{site_title}';

    return <<<HTML
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$title}</title>
HTML;
}

/**
 * @param array<int, string> $tokens
 */
function removeTokenText(string &$html, array $tokens): void
{
    foreach ($tokens as $token) {
        $html = str_replace($token, '', $html);
    }
}

function clearChildren(DOMElement $element): void
{
    while ($element->firstChild !== null) {
        $element->removeChild($element->firstChild);
    }
}
