<?php

declare(strict_types=1);

$projectRoot = dirname(__DIR__, 2);
$outputDirectory = $projectRoot . '/dev/docs';
$frameworkOutput = $outputDirectory . '/framework-reference.html';
$legacyOutput = $outputDirectory . '/services-reference.html';

require_once $projectRoot . '/vendor/autoload.php';
require_once $projectRoot . '/config/bootstrap.php';

$classes = collectProjectClasses($projectRoot);
$html = buildHtml($classes, $projectRoot);

if (! is_dir($outputDirectory)) {
    mkdir($outputDirectory, 0777, true);
}

file_put_contents($frameworkOutput, $html);
file_put_contents($legacyOutput, $html);

echo "HTML framework referansi olusturuldu: {$frameworkOutput}" . PHP_EOL;

/**
 * App ve Core altindaki siniflari toplar.
 *
 * @param string $projectRoot Proje kok dizini.
 * @return array<int, ReflectionClass<object>>
 */
function collectProjectClasses(string $projectRoot): array
{
    $classes = [];
    $directories = [
        $projectRoot . '/app',
        $projectRoot . '/core',
    ];

    foreach (discoverClassNames($directories) as $className) {
        if (! class_exists($className)) {
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
 * Verilen dizinlerdeki PHP sinif adlarini kesfeder.
 *
 * @param array<int, string> $directories Taranacak dizinler.
 * @return array<int, string>
 */
function discoverClassNames(array $directories): array
{
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
 * Dosyadan tam sinif adini cikarir.
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
 * Namespace metnini tokenlerden okur.
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
 * Token listesindeki bir sonraki sinif adini bulur.
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
 * Tum framework siniflari icin HTML referansi olusturur.
 *
 * @param array<int, ReflectionClass<object>> $classes Sinif listesi.
 * @param string $projectRoot Proje kok dizini.
 * @return string
 */
function buildHtml(array $classes, string $projectRoot): string
{
    $groups = [];

    foreach ($classes as $class) {
        $groups[groupLabel($class->getName())][] = $class;
    }

    ksort($groups);

    $navigation = [];
    $sections = [];
    $totalMethods = 0;

    foreach ($groups as $groupLabel => $groupClasses) {
        $navigation[] = '<li class="nav-group" data-search="' . escape(strtolower($groupLabel)) . '"><strong>' . escape($groupLabel) . '</strong><ul>';
        $sections[] = '<section class="group"><h2>' . escape($groupLabel) . '</h2>';

        foreach ($groupClasses as $class) {
            $id = slug($class->getName());
            $methods = declaredPublicMethods($class);
            $methodNames = implode(' ', array_map(static fn (ReflectionMethod $method): string => $method->getName(), $methods));
            $navSearch = strtolower($class->getName() . ' ' . $class->getShortName() . ' ' . $methodNames);

            $navigation[] = '<li class="nav-class" data-search="' . escape($navSearch) . '"><a href="#' . $id . '">' . escape($class->getShortName()) . '</a>';
            $navigation[] = '<ul>';

            foreach ($methods as $method) {
                $navigation[] = '<li class="nav-method" data-search="' . escape(strtolower($class->getShortName() . ' ' . $method->getName())) . '"><a href="#' . $id . '-' . $method->getName() . '">' . escape($method->getName()) . '</a></li>';
            }

            $navigation[] = '</ul></li>';
            $sections[] = buildClassSection($class, $projectRoot, $methods, $id);
            $totalMethods += count($methods);
        }

        $navigation[] = '</ul></li>';
        $sections[] = '</section>';
    }

    return '<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Framework Referansi</title>
    <style>
        :root {
            --bg: #f4efe6;
            --panel: #fffaf2;
            --ink: #1f2430;
            --muted: #6e7380;
            --line: #ddd2c1;
            --accent: #9c4f2f;
            --accent-soft: #f4d9ca;
            --code: #2c303a;
            --input: #fff8ef;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Georgia, "Times New Roman", serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top right, #f3d9bb 0, transparent 24rem),
                linear-gradient(180deg, #f8f4ec 0%, var(--bg) 100%);
        }
        .layout {
            display: grid;
            grid-template-columns: 340px 1fr;
            min-height: 100vh;
        }
        aside {
            position: sticky;
            top: 0;
            align-self: start;
            height: 100vh;
            overflow: auto;
            padding: 2rem 1.5rem;
            border-right: 1px solid var(--line);
            background: rgba(255, 250, 242, 0.94);
            backdrop-filter: blur(12px);
        }
        main {
            padding: 2rem;
        }
        h1, h2, h3, h4 {
            margin: 0 0 0.8rem;
            line-height: 1.15;
        }
        h1 { font-size: 2.5rem; }
        h2 { font-size: 1.5rem; margin-top: 2rem; }
        h3 { font-size: 1.25rem; }
        h4 { font-size: 1rem; margin-top: 1rem; color: var(--accent); }
        p, li { line-height: 1.55; }
        .lead {
            max-width: 74ch;
            color: var(--muted);
            margin-bottom: 1.5rem;
        }
        .card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 1.25rem;
            box-shadow: 0 10px 30px rgba(76, 56, 37, 0.08);
            margin-bottom: 1.25rem;
        }
        .hero-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.9rem;
            margin-top: 1rem;
        }
        .hero-stat {
            background: #fff2e5;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 0.9rem;
        }
        .hero-stat strong {
            display: block;
            font-size: 1.4rem;
            color: var(--accent);
        }
        .meta {
            color: var(--muted);
            font-size: 0.92rem;
            margin-bottom: 0.6rem;
        }
        .chip {
            display: inline-block;
            padding: 0.2rem 0.55rem;
            margin-right: 0.4rem;
            margin-bottom: 0.4rem;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 0.85rem;
        }
        .search-box {
            margin: 1rem 0 1.2rem;
        }
        .search-box input {
            width: 100%;
            padding: 0.85rem 1rem;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: var(--input);
            font: inherit;
            color: var(--ink);
        }
        .search-note {
            color: var(--muted);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        code, pre {
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
            color: var(--code);
        }
        pre {
            margin: 0.6rem 0 0;
            background: #f7efe4;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 0.9rem 1rem;
            overflow: auto;
            white-space: pre-wrap;
        }
        .method {
            border-top: 1px dashed var(--line);
            padding-top: 1rem;
            margin-top: 1rem;
        }
        .tree, .tree ul {
            list-style: none;
            padding-left: 1rem;
            margin: 0.35rem 0 0;
        }
        .tree > li { padding-left: 0; }
        .tree a {
            color: var(--ink);
            text-decoration: none;
        }
        .tree a:hover { color: var(--accent); }
        .hidden { display: none !important; }
        @media (max-width: 980px) {
            .layout { grid-template-columns: 1fr; }
            aside {
                position: static;
                height: auto;
                border-right: 0;
                border-bottom: 1px solid var(--line);
            }
            .hero-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="layout">
        <aside>
            <h2>Sinif Agaci</h2>
            <p class="lead">App ve core altindaki siniflar buradan aranabilir. Arama sinif adinda, namespace icinde ve metod adlarinda calisir.</p>
            <div class="search-box">
                <input id="nav-search" type="search" placeholder="Sinif, namespace veya metod ara">
                <div class="search-note" id="search-note">Tum framework listeleniyor.</div>
            </div>
            <ul class="tree" id="nav-tree">' . implode('', $navigation) . '</ul>
        </aside>
        <main>
            <header class="card">
                <h1>Framework Referansi</h1>
                <p class="lead">Bu HTML dosyasi tum framework siniflarini, metod agacini ve temel kullanim orneklerini tek yerde toplar. Ozellikle yeni gelistirme yaparken hangi sinif nerede, hangi metod ne yapiyor ve nasil kullaniliyor sorularina hizli cevap verir.</p>
                <div class="hero-grid">
                    <div class="hero-stat"><strong>' . count($classes) . '</strong>sinif</div>
                    <div class="hero-stat"><strong>' . $totalMethods . '</strong>public metod</div>
                    <div class="hero-stat"><strong>App + Core</strong>tek referans</div>
                </div>
            </header>
            <div id="sections">' . implode('', $sections) . '</div>
        </main>
    </div>
    <script>
        const input = document.getElementById("nav-search");
        const note = document.getElementById("search-note");
        const cards = Array.from(document.querySelectorAll("[data-search-card]"));
        const navClasses = Array.from(document.querySelectorAll(".nav-class"));
        const navMethods = Array.from(document.querySelectorAll(".nav-method"));
        const navGroups = Array.from(document.querySelectorAll(".nav-group"));

        function applySearch() {
            const query = input.value.trim().toLowerCase();
            let visibleCards = 0;

            cards.forEach((card) => {
                const match = query === "" || card.dataset.searchCard.includes(query);
                card.classList.toggle("hidden", !match);
                if (match) {
                    visibleCards += 1;
                }
            });

            navMethods.forEach((item) => {
                const match = query === "" || item.dataset.search.includes(query);
                item.classList.toggle("hidden", !match);
            });

            navClasses.forEach((item) => {
                const classMatch = query === "" || item.dataset.search.includes(query);
                const hasVisibleMethod = Array.from(item.querySelectorAll(".nav-method")).some((node) => !node.classList.contains("hidden"));
                const visible = classMatch || hasVisibleMethod;
                item.classList.toggle("hidden", !visible);
            });

            navGroups.forEach((item) => {
                const hasVisibleChild = Array.from(item.querySelectorAll(".nav-class")).some((node) => !node.classList.contains("hidden"));
                item.classList.toggle("hidden", !hasVisibleChild);
            });

            note.textContent = query === ""
                ? "Tum framework listeleniyor."
                : visibleCards + " sinif eslesti.";
        }

        input.addEventListener("input", applySearch);
    </script>
</body>
</html>';
}

/**
 * Tek bir sinif icin HTML bolumu uretir.
 *
 * @param ReflectionClass<object> $class Sinif.
 * @param string $projectRoot Proje kok dizini.
 * @param array<int, ReflectionMethod> $methods Sinifa ait public metodlar.
 * @param string $id HTML anchor kimligi.
 * @return string
 */
function buildClassSection(ReflectionClass $class, string $projectRoot, array $methods, string $id): string
{
    $fileName = (string) $class->getFileName();
    $relativeFile = ltrim(str_replace($projectRoot, '', $fileName), '/');
    $classSummary = summaryFromDocComment($class->getDocComment() ?: '');
    $methodNames = implode(' ', array_map(static fn (ReflectionMethod $method): string => $method->getName(), $methods));
    $searchText = strtolower($class->getName() . ' ' . $class->getShortName() . ' ' . $classSummary . ' ' . $methodNames);
    $chips = chipsForClass($class->getName());

    $html = '<article class="card" id="' . $id . '" data-search-card="' . escape($searchText) . '">';
    $html .= '<h3>' . escape($class->getShortName()) . '</h3>';
    $html .= '<div class="meta">' . escape($class->getName()) . '</div>';
    $html .= '<div class="meta"><a href="/Users/mucahityenen/Desktop/Php-framework/' . escape($relativeFile) . '">' . escape($relativeFile) . '</a></div>';
    $html .= implode('', $chips);

    if ($classSummary !== '') {
        $html .= '<p>' . escape($classSummary) . '</p>';
    }

    $html .= '<h4>Sinif Kullanim Ornegi</h4>';
    $html .= '<pre><code>' . escape(buildClassUsageExample($class)) . '</code></pre>';

    if ($methods !== []) {
        $html .= '<h4>Metod Agaci</h4><ul>';
        foreach ($methods as $method) {
            $html .= '<li><code>' . escape(methodSignature($method)) . '</code></li>';
        }
        $html .= '</ul>';
    }

    foreach ($methods as $method) {
        $methodSummary = summaryFromDocComment($method->getDocComment() ?: '');
        $html .= '<div class="method" id="' . $id . '-' . $method->getName() . '">';
        $html .= '<h4>' . escape($method->getName()) . '</h4>';
        $html .= '<div class="meta"><code>' . escape(methodSignature($method)) . '</code></div>';

        if ($methodSummary !== '') {
            $html .= '<p>' . escape($methodSummary) . '</p>';
        }

        $html .= '<pre><code>' . escape(buildMethodUsageExample($class, $method)) . '</code></pre>';
        $html .= '</div>';
    }

    $html .= '</article>';

    return $html;
}

/**
 * Sinifa ait sadece o sinifta tanimli public metodlari dondurur.
 *
 * @param ReflectionClass<object> $class Sinif.
 * @return array<int, ReflectionMethod>
 */
function declaredPublicMethods(ReflectionClass $class): array
{
    $methods = array_values(array_filter(
        $class->getMethods(ReflectionMethod::IS_PUBLIC),
        static fn (ReflectionMethod $method): bool => $method->getDeclaringClass()->getName() === $class->getName()
    ));

    usort(
        $methods,
        static fn (ReflectionMethod $left, ReflectionMethod $right): int => $left->getStartLine() <=> $right->getStartLine()
    );

    return $methods;
}

/**
 * Namespace'e gore grup etiketi uretir.
 *
 * @param string $className Tam sinif adi.
 * @return string
 */
function groupLabel(string $className): string
{
    $parts = explode('\\', $className);

    if (count($parts) >= 3) {
        return $parts[0] . ' / ' . $parts[1] . ' / ' . $parts[2];
    }

    if (count($parts) >= 2) {
        return $parts[0] . ' / ' . $parts[1];
    }

    return $parts[0] ?? 'Genel';
}

/**
 * Sinif icin etiket chip'lerini uretir.
 *
 * @param string $className Tam sinif adi.
 * @return array<int, string>
 */
function chipsForClass(string $className): array
{
    $chips = [];

    if (str_starts_with($className, 'App\\')) {
        $chips[] = '<span class="chip">App</span>';
    }

    if (str_starts_with($className, 'Core\\')) {
        $chips[] = '<span class="chip">Core</span>';
    }

    foreach (['Controllers', 'Services', 'Validation', 'Http', 'View', 'Security', 'Localization', 'Logging', 'Orm'] as $segment) {
        if (str_contains($className, '\\' . $segment . '\\') || str_ends_with($className, '\\' . $segment)) {
            $chips[] = '<span class="chip">' . escape($segment) . '</span>';
        }
    }

    return $chips;
}

/**
 * Docblock icinden kisa ozet cikarir.
 *
 * @param string $docComment Docblock metni.
 * @return string
 */
function summaryFromDocComment(string $docComment): string
{
    if ($docComment === '') {
        return '';
    }

    $lines = preg_split('/\R/', $docComment) ?: [];
    $clean = [];

    foreach ($lines as $line) {
        $line = trim($line);
        $line = preg_replace('/^\/\*\*?/', '', $line) ?? $line;
        $line = preg_replace('/^\*\/?/', '', $line) ?? $line;
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '@')) {
            continue;
        }

        $clean[] = $line;
    }

    return $clean[0] ?? '';
}

/**
 * Sinif icin basit inject ornegini dondurur.
 *
 * @param ReflectionClass<object> $class Sinif.
 * @return string
 */
function buildClassUsageExample(ReflectionClass $class): string
{
    if ($class->getName() === 'Core\\Orm\\Models') {
        return "use Core\\Orm\\Models;\n\n\$user = Models::get('users');\n\$user->name = 'Mucahit';\n\$user->email = 'mucahit@example.com';\n\$user->save();\n";
    }

    if ($class->getShortName() === 'GenericModel') {
        return "// GenericModel dogrudan kullanilmaz.\n// Bunun yerine models::get('tablo_adi') ya da Models::get('tablo_adi') kullanilir.\n\n\$user = models::get('users');\n";
    }

    $short = $class->getShortName();
    $variable = '$' . lcfirst($short);

    return "use {$class->getName()};\n\npublic function __construct(\n    private readonly {$short} {$variable}\n) {\n}\n";
}

/**
 * Metod icin kullanim ornegi olusturur.
 *
 * @param ReflectionClass<object> $class Sinif.
 * @param ReflectionMethod $method Metod.
 * @return string
 */
function buildMethodUsageExample(ReflectionClass $class, ReflectionMethod $method): string
{
    if ($class->getName() === 'Core\\Orm\\Models') {
        return buildModelsMethodUsageExample($method);
    }

    if ($method->isConstructor()) {
        return buildClassUsageExample($class);
    }

    $variable = '$this->' . lcfirst($class->getShortName());
    $arguments = [];

    foreach ($method->getParameters() as $parameter) {
        $arguments[] = sampleValueForParameter($parameter);
    }

    $call = $variable . '->' . $method->getName() . '(' . implode(', ', $arguments) . ')';

    return match ((string) $method->getReturnType()) {
        'void' => $call . ';',
        default => '$result = ' . $call . ';',
    };
}

/**
 * Models sinifi icin gercek kullanim ornekleri uretir.
 *
 * @param ReflectionMethod $method ORM metodu.
 * @return string
 */
function buildModelsMethodUsageExample(ReflectionMethod $method): string
{
    return match ($method->getName()) {
        '__construct' => "// Models sinifi dogrudan new edilmez.\n\$user = models::get('users');",
        'get' => "\$user = Models::get('users');",
        'save' => "\$user = models::get('users');\n\$user->name = 'Mucahit';\n\$user->email = 'mucahit@example.com';\n\$user->save();",
        'update' => "\$user = models::get('users');\n\$user->id = 1;\n\$user->name = 'Yeni Ad';\n\$user->update();",
        'delete' => "\$user = models::get('users');\n\$user->id = 1;\n\$user->delete();",
        'find' => "\$user = models::get('users')->find(1);",
        'where' => "\$result = models::get('users')\n    ->where('status', 'active')\n    ->orderBy('id', 'DESC')\n    ->all();",
        'orWhere' => "\$result = models::get('users')\n    ->where('status', 'active')\n    ->orWhere('status', 'pending')\n    ->all();",
        'whereLike' => "\$result = models::get('users')\n    ->whereLike('email', 'example')\n    ->all();",
        'whereIn' => "\$result = models::get('users')\n    ->whereIn('role', [1, 2])\n    ->all();",
        'orderBy' => "\$result = models::get('users')\n    ->orderBy('id', 'DESC')\n    ->all();",
        'limit' => "\$result = models::get('users')\n    ->orderBy('id', 'DESC')\n    ->limit(10)\n    ->all();",
        'first' => "\$user = models::get('users')\n    ->where('id', 1)\n    ->first();",
        'all' => "\$users = models::get('users')->all();",
        'count' => "\$count = models::get('users')\n    ->where('status', 'active')\n    ->count();",
        'exists' => "\$exists = models::get('users')\n    ->where('email', 'mucahit@example.com')\n    ->exists();",
        'pluck' => "\$emails = models::get('users')->pluck('email');",
        'paginate' => "\$page = models::get('users')\n    ->orderBy('id', 'DESC')\n    ->paginate(1, 10);",
        'toarray' => "\$data = models::get('users')\n    ->where('id', 1)\n    ->first()\n    ->toarray();",
        'filter' => "\$filtered = models::get('users')\n    ->where('id', 1)\n    ->first()\n    ->filter(['id', 'name', 'email']);",
        'fill' => "\$user = models::get('users');\n\$user->fill((object) ['name' => 'Mucahit', 'email' => 'mucahit@example.com']);",
        'runSQL' => "\$rows = models::get('users')->runSQL(\n    'SELECT * FROM users WHERE status = :status',\n    [':status' => 'active']\n);",
        default => "\$model = models::get('users');\n// {$method->getName()} icin ornek kullanim gerektiginde burada genisletebilirsiniz.",
    };
}

/**
 * Parametre icin ornek deger uretir.
 *
 * @param ReflectionParameter $parameter Metod parametresi.
 * @return string
 */
function sampleValueForParameter(ReflectionParameter $parameter): string
{
    $name = strtolower($parameter->getName());
    $type = $parameter->getType();
    $typeName = $type instanceof ReflectionNamedType ? $type->getName() : 'mixed';

    if ($typeName === 'array') {
        return match (true) {
            str_contains($name, 'file') => "['name' => 'ornek.txt', 'tmp_name' => '/tmp/ornek.txt', 'size' => 123, 'type' => 'text/plain', 'error' => 0]",
            str_contains($name, 'payload'), str_contains($name, 'data') => "['key' => 'value']",
            str_contains($name, 'filters') => "['q' => '', 'status' => '']",
            default => '[]',
        };
    }

    if ($typeName === 'int') {
        return '1';
    }

    if ($typeName === 'bool') {
        return 'true';
    }

    if ($typeName === 'float') {
        return '1.0';
    }

    if ($typeName === 'string') {
        return match (true) {
            str_contains($name, 'path') => "'/admin/example'",
            str_contains($name, 'email') => "'ornek@example.com'",
            str_contains($name, 'password') => "'010101'",
            str_contains($name, 'channel') => "'site'",
            str_contains($name, 'template') => "'pages/example'",
            str_contains($name, 'subject') => "'Test konusu'",
            str_contains($name, 'message') => "'Test mesaji'",
            str_contains($name, 'directory') => "'common'",
            str_contains($name, 'locale') => "'tr'",
            str_contains($name, 'key') => "'{{Ornek metin}}'",
            str_contains($name, 'name') => "'Ornek'",
            default => "'deger'",
        };
    }

    if ($typeName === 'mixed') {
        return "'deger'";
    }

    return '$' . $parameter->getName();
}

/**
 * Metod imzasini tek satira cevirir.
 *
 * @param ReflectionMethod $method Reflected method.
 * @return string
 */
function methodSignature(ReflectionMethod $method): string
{
    $parameters = array_map(
        static function (ReflectionParameter $parameter): string {
            $type = $parameter->getType();
            $typeString = $type instanceof ReflectionType ? $type . ' ' : '';
            $default = '';

            if ($parameter->isDefaultValueAvailable()) {
                $default = ' = ' . var_export($parameter->getDefaultValue(), true);
            }

            return $typeString . '$' . $parameter->getName() . $default;
        },
        $method->getParameters()
    );

    $returnType = $method->hasReturnType() ? ': ' . $method->getReturnType() : '';

    return $method->getName() . '(' . implode(', ', $parameters) . ')' . $returnType;
}

/**
 * HTML guvenli cikti icin escape uygular.
 *
 * @param string $value Ham metin.
 * @return string
 */
function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Anchor icin slug olusturur.
 *
 * @param string $value Ham metin.
 * @return string
 */
function slug(string $value): string
{
    $value = strtolower($value);
    $value = str_replace(['\\', '/'], '-', $value);

    return preg_replace('/[^a-z0-9\-]+/', '-', $value) ?? $value;
}
