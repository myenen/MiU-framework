<?php

declare(strict_types=1);

namespace Core\View;

use Core\Localization\LanguageService;
use RuntimeException;

/**
 * Placeholder token'lari kacislanmis verilerle degistiren HTML sablon goruntuleyicisi.
 */
final class View
{
    /**
     * @param string $basePath View sablonlarinin tutuldugu temel yol.
     * @param string $defaultLayout Varsayilan layout sablon adi.
     * @param array $sharedData Her render islemine eklenen ortak degerler.
     */
    public function __construct(
        private readonly string $basePath,
        private readonly string $defaultLayout,
        private readonly array $sharedData = [],
        private readonly ?LanguageService $language = null
    ) {
    }

    /**
     * Bir view'i layout icinde render eder.
     *
     * @param string $template View sablon adi.
     * @param array $data Sablon verisi.
     * @param string|null $layout Opsiyonel layout ezmesi.
     * @return string
     */
    public function render(string $template, array $data = [], ?string $layout = null): string
    {
        $viewData = array_merge($this->sharedData, $data);
        $content = $this->renderFile($template, $viewData);
        $layoutToUse = $layout ?? $this->defaultLayout;

        return $this->renderFile($layoutToUse, array_merge($viewData, [
            'content' => new RawValue($content),
        ]));
    }

    /**
     * Bir sablon dosyasini yukler ve parse eder.
     *
     * @param string $template Sablon adi.
     * @param array $data Sablon verisi.
     * @return string
     */
    private function renderFile(string $template, array $data): string
    {
        $file = $this->basePath . '/' . str_replace('.', '/', $template) . '.html';

        if (! is_file($file)) {
            throw new RuntimeException("View file not found: {$file}");
        }

        return $this->parseTemplate((string) file_get_contents($file), $data);
    }

    /**
     * Verilen view verisini kullanarak placeholder token'larini parse eder.
     *
     * @param string $template Sablon icerigi.
     * @param array $data Sablon verisi.
     * @return string
     */
    private function parseTemplate(string $template, array $data): string
    {
        $template = $this->parseConditionalBlocks($template, $data);

        $template = (string) preg_replace_callback('/\{\>\s*([^{}]+)\}/u', function (array $matches) use ($data): string {
            $partial = trim($matches[1]);

            if ($partial === '') {
                return '';
            }

            return $this->renderFile($partial, $data);
        }, $template);

        $template = (string) preg_replace_callback('/\{\{([^{}]+)\}\}/u', function (array $matches): string {
            $key = trim($matches[1]);
            $value = $this->language?->translatePlaceholder($key);

            if ($value instanceof RawValue) {
                return $value->value();
            }

            if (is_scalar($value) || $value === null) {
                return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            }

            return '';
        }, $template);

        return (string) preg_replace_callback('/\{([^{}]+)\}/u', function (array $matches) use ($data): string {
            $key = trim($matches[1]);
            $helperValue = $this->resolveHelper($key, $data);

            if ($helperValue !== null) {
                return htmlspecialchars($helperValue, ENT_QUOTES, 'UTF-8');
            }

            $value = $data[$key] ?? null;

            if ($value instanceof RawValue) {
                return $value->value();
            }

            if (is_scalar($value) || $value === null) {
                return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            }

            return '';
        }, $template);
    }

    /**
     * Kosul bloklarini parse eder.
     *
     * @param string $template Sablon icerigi.
     * @param array<string, mixed> $data Sablon verisi.
     * @return string
     */
    private function parseConditionalBlocks(string $template, array $data): string
    {
        $previous = null;
        $current = $template;

        while ($previous !== $current) {
            $previous = $current;
            $current = (string) preg_replace_callback('/\{\?([^{}]+)\}(.*?)\{\/\1\}/su', function (array $matches) use ($data): string {
                $condition = trim($matches[1]);
                $content = (string) ($matches[2] ?? '');

                return $this->resolveCondition($condition, $data)
                    ? $this->parseConditionalBlocks($content, $data)
                    : '';
            }, $current);
        }

        return $current;
    }

    /**
     * Ozel helper placeholder'larini cozer.
     *
     * @param string $key Placeholder anahtari.
     * @param array<string, mixed> $data Sablon verisi.
     * @return string|null
     */
    private function resolveHelper(string $key, array $data): ?string
    {
        if (str_starts_with($key, 'url:')) {
            return $this->joinUrl((string) ($data['app_url'] ?? ''), substr($key, 4));
        }

        if (str_starts_with($key, 'asset:')) {
            return $this->joinUrl((string) ($data['assets_url'] ?? ''), substr($key, 6));
        }

        return null;
    }

    /**
     * Kosul metnini truthy/permission mantigiyla cozer.
     *
     * @param string $condition Kosul metni.
     * @param array<string, mixed> $data Sablon verisi.
     * @return bool
     */
    private function resolveCondition(string $condition, array $data): bool
    {
        if ($condition === '') {
            return false;
        }

        if (str_starts_with($condition, '!')) {
            return ! $this->resolveCondition(substr($condition, 1), $data);
        }

        if (str_starts_with($condition, 'can:')) {
            return $this->hasPermission(substr($condition, 4), $data);
        }

        $value = $data[$condition] ?? null;

        if (is_bool($value)) {
            return $value;
        }

        if (is_array($value)) {
            return $value !== [];
        }

        return ! in_array($value, [null, '', '0', 0, false], true);
    }

    /**
     * Mevcut permission listesi icinde verilen yetkinin karsilanip karsilanmadigini kontrol eder.
     *
     * @param string $required Gerekli yetki.
     * @param array<string, mixed> $data Sablon verisi.
     * @return bool
     */
    private function hasPermission(string $required, array $data): bool
    {
        $required = trim($required);

        if ($required === '') {
            return false;
        }

        $permissions = $data['current_permissions'] ?? [];

        if (! is_array($permissions)) {
            return false;
        }

        foreach ($permissions as $permission) {
            $permission = trim((string) $permission);

            if ($permission === '' ) {
                continue;
            }

            if ($permission === 'all' || $permission === '*') {
                return true;
            }

            if ($permission === $required) {
                return true;
            }

            if (str_ends_with($permission, '.*')) {
                $prefix = substr($permission, 0, -2);

                if ($prefix !== '' && str_starts_with($required, $prefix . '.')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Temel URL ve yol parcasi ile tam adres olusturur.
     *
     * @param string $base Temel URL.
     * @param string $path Yol parcasi.
     * @return string
     */
    private function joinUrl(string $base, string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return rtrim($base, '/');
        }

        if (preg_match('/^https?:\/\//i', $path) === 1) {
            return $path;
        }

        $normalizedPath = '/' . ltrim($path, '/');

        if ($base === '') {
            return $normalizedPath;
        }

        return rtrim($base, '/') . $normalizedPath;
    }
}
