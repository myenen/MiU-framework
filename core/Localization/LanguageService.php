<?php

declare(strict_types=1);

namespace Core\Localization;

use Core\Http\Request;
use Core\Orm\Models;
use Core\Session;
use Throwable;

/**
 * Aktif dili ve veritabanindaki ceviri anahtarlarini yonetir.
 */
final class LanguageService
{
    private ?array $activeLanguage = null;
    private ?array $translations = null;
    private ?array $fallbackTranslations = null;
    private array $languages = [];
    private array $registeredPlaceholders = [];

    /**
     * @param Session $session Aktif dil secimini oturumda saklayan servis.
     * @param Request $request Mevcut HTTP istegi.
     * @param array $config Dil ayarlari.
     */
    public function __construct(
        private readonly Session $session,
        private readonly Request $request,
        private readonly array $config = []
    ) {
    }

    /**
     * Aktif dil kodunu dondurur.
     *
     * @return string
     */
    public function locale(): string
    {
        return (string) ($this->activeLanguage()['code'] ?? (string) ($this->config['default_locale'] ?? 'tr'));
    }

    /**
     * Aktif dil kaydini dondurur.
     *
     * @return array<string, mixed>
     */
    public function activeLanguage(): array
    {
        if ($this->activeLanguage !== null) {
            return $this->activeLanguage;
        }

        try {
            $languages = $this->availableLanguages();
            $requestedLocale = trim((string) $this->request->query('lang', ''));
            $sessionLocale = trim((string) $this->session->get('app.locale', ''));
            $defaultLocale = (string) ($this->config['default_locale'] ?? 'tr');

            $selected = $this->matchLanguage($languages, $requestedLocale)
                ?? $this->matchLanguage($languages, $sessionLocale)
                ?? $this->defaultLanguage($languages)
                ?? [
                    'id' => 0,
                    'code' => $defaultLocale,
                    'name' => strtoupper($defaultLocale),
                    'is_default' => 1,
                ];

            $this->session->put('app.locale', (string) $selected['code']);

            return $this->activeLanguage = $selected;
        } catch (Throwable) {
            return $this->activeLanguage = [
                'id' => 0,
                'code' => (string) ($this->config['default_locale'] ?? 'tr'),
                'name' => 'Default',
                'is_default' => 1,
            ];
        }
    }

    /**
     * Verilen anahtar icin aktif dildeki karsiligi dondurur.
     *
     * @param string $key Ceviri anahtari.
     * @param string $default Ceviri bulunmazsa donulecek deger.
     * @return string
     */
    public function get(string $key, string $default = ''): string
    {
        $translations = $this->translations();

        if (array_key_exists($key, $translations)) {
            $value = (string) $translations[$key];

            if ($value !== '') {
                return $value;
            }
        }

        $fallback = $this->fallbackTranslations();
        if (array_key_exists($key, $fallback)) {
            return (string) $fallback[$key];
        }

        return $default;
    }

    /**
     * Placeholder icindeki metni ceviri anahtari olarak cozer.
     *
     * @param string $placeholder Template icindeki placeholder metni.
     * @return string
     */
    public function translatePlaceholder(string $placeholder): string
    {
        $text = trim($placeholder);
        if ($text === '') {
            return '';
        }

        try {
            $key = '{{' . $text . '}}';
            $this->ensurePlaceholderTranslation($key, $text);

            return $this->get($key, $text);
        } catch (Throwable) {
            return $text;
        }
    }

    /**
     * Tum aktif dil cevirilerini dondurur.
     *
     * @return array<string, string>
     */
    public function translations(): array
    {
        if ($this->translations !== null) {
            return $this->translations;
        }

        $languageId = (int) ($this->activeLanguage()['id'] ?? 0);
        if ($languageId <= 0) {
            return $this->translations = [];
        }

        return $this->translations = $this->loadTranslations($languageId);
    }

    /**
     * Varsayilan dildeki cevirileri dondurur.
     *
     * @return array<string, string>
     */
    private function fallbackTranslations(): array
    {
        if ($this->fallbackTranslations !== null) {
            return $this->fallbackTranslations;
        }

        try {
            $defaultLanguage = $this->defaultLanguage($this->availableLanguages());
            $activeLanguageId = (int) ($this->activeLanguage()['id'] ?? 0);
            $defaultLanguageId = (int) ($defaultLanguage['id'] ?? 0);

            if ($defaultLanguageId <= 0 || $defaultLanguageId === $activeLanguageId) {
                return $this->fallbackTranslations = [];
            }

            return $this->fallbackTranslations = $this->loadTranslations($defaultLanguageId);
        } catch (Throwable) {
            return $this->fallbackTranslations = [];
        }
    }

    /**
     * Veritabanindan aktif dilleri listeler.
     *
     * @return array<int, array<string, mixed>>
     */
    private function availableLanguages(): array
    {
        if ($this->languages !== []) {
            return $this->languages;
        }

        $rows = Models::get('languages')
            ->where('is_active', 1)
            ->orderBy('is_default', 'DESC')
            ->orderBy('id', 'ASC')
            ->all();

        return $this->languages = $this->normalizeRows($rows);
    }

    /**
     * Dil listesinde istenen kodu arar.
     *
     * @param array<int, array<string, mixed>> $languages Dil listesi.
     * @param string $code Aranan dil kodu.
     * @return array<string, mixed>|null
     */
    private function matchLanguage(array $languages, string $code): ?array
    {
        if ($code === '') {
            return null;
        }

        foreach ($languages as $language) {
            if ((string) ($language['code'] ?? '') === $code) {
                return $language;
            }
        }

        return null;
    }

    /**
     * Varsayilan dili dil listesi icinden bulur.
     *
     * @param array<int, array<string, mixed>> $languages Dil listesi.
     * @return array<string, mixed>|null
     */
    private function defaultLanguage(array $languages): ?array
    {
        foreach ($languages as $language) {
            if ((int) ($language['is_default'] ?? 0) === 1) {
                return $language;
            }
        }

        return $languages[0] ?? null;
    }

    /**
     * Belirli bir dil kaydi icin ceviri verilerini yukler.
     *
     * @param int $languageId Dil kaydi id degeri.
     * @return array<string, string>
     */
    private function loadTranslations(int $languageId): array
    {
        $rows = Models::get('language_translations')
            ->where('language_id', $languageId)
            ->all();

        $translations = [];

        foreach ($this->normalizeRows($rows) as $row) {
            $key = (string) ($row['translation_key'] ?? '');
            if ($key === '') {
                continue;
            }

            $translations[$key] = (string) ($row['translation_value'] ?? '');
        }

        return $translations;
    }

    /**
     * Placeholder anahtari icin varsayilan ve bos ceviri kayitlarini olusturur.
     *
     * @param string $key Placeholder anahtari.
     * @param string $value Varsayilan dilde saklanacak gorunen metin.
     * @return void
     */
    private function ensurePlaceholderTranslation(string $key, string $value): void
    {
        if (isset($this->registeredPlaceholders[$key])) {
            return;
        }

        $languages = $this->availableLanguages();
        $defaultLanguage = $this->defaultLanguage($languages);
        $defaultLanguageId = (int) ($defaultLanguage['id'] ?? 0);
        $timestamp = time();

        if ($defaultLanguageId > 0) {
            $this->insertTranslationIfMissing($defaultLanguageId, $key, $value, $timestamp);
        }

        foreach ($languages as $language) {
            $languageId = (int) ($language['id'] ?? 0);
            if ($languageId <= 0 || $languageId === $defaultLanguageId) {
                continue;
            }

            $this->insertTranslationIfMissing($languageId, $key, '', $timestamp);
        }

        if ($defaultLanguageId > 0) {
            $this->fallbackTranslations = null;
            $this->translations = null;
        }

        $this->registeredPlaceholders[$key] = true;
    }

    /**
     * Ceviri kaydi yoksa yeni satir olusturur.
     *
     * @param int $languageId Dil kaydi id degeri.
     * @param string $key Ceviri anahtari.
     * @param string $value Ceviri degeri.
     * @param int $timestamp Zaman damgasi.
     * @return void
     */
    private function insertTranslationIfMissing(int $languageId, string $key, string $value, int $timestamp): void
    {
        $translations = Models::get('language_translations');
        $exists = $translations
            ->where('language_id', $languageId)
            ->where('translation_key', $key)
            ->exists();

        if ($exists) {
            return;
        }

        $translations->runSQL(
            'INSERT INTO language_translations (language_id, translation_key, translation_value, created_at, updated_at)
             VALUES (:language_id, :translation_key, :translation_value, :created_at, :updated_at)',
            [
                ':language_id' => $languageId,
                ':translation_key' => $key,
                ':translation_value' => $value,
                ':created_at' => $timestamp,
                ':updated_at' => $timestamp,
            ]
        );
    }

    /**
     * Model sonucunu tutarli bir satir listesine donusturur.
     *
     * @param object|array|false $rows Model sonuc kumesi.
     * @return array<int, array<string, mixed>>
     */
    private function normalizeRows(object|array|false $rows): array
    {
        if ($rows === false) {
            return [];
        }

        $items = is_array($rows) ? $rows : [$rows];
        $normalized = [];

        foreach ($items as $item) {
            if (! is_object($item)) {
                continue;
            }

            $normalized[] = get_object_vars($item);
        }

        return $normalized;
    }
}
