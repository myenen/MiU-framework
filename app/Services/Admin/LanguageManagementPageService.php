<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Services\AuthService;
use Core\Orm\Models;
use Core\Security\Csrf;
use Core\Services\BasePageService;
use Core\Services\ServiceResult;
use Core\Session;

/**
 * Admin dil ve ceviri ekranlarinin servis katmanini yonetir.
 */
final class LanguageManagementPageService extends BasePageService
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly Session $session,
        private readonly Csrf $csrf
    ) {
    }

    /**
     * @param string $path
     * @param array<string, mixed> $filters
     * @return ServiceResult
     */
    public function index(string $path, array $filters = []): ServiceResult
    {
        $guard = $this->guard();
        if (! $guard->isSuccess()) {
            return $guard;
        }

        $languages = $this->languageRows();
        $grouped = $this->groupTranslationsByKey($this->translationRows());
        $query = mb_strtolower(trim((string) ($filters['q'] ?? '')));
        $missingOnly = (string) ($filters['missing'] ?? '') === '1';

        if ($query !== '') {
            $grouped = array_values(array_filter($grouped, static function (array $item) use ($query): bool {
                $haystack = mb_strtolower(implode(' ', [
                    (string) ($item['translation_key'] ?? ''),
                    (string) ($item['preview'] ?? ''),
                ]));

                return str_contains($haystack, $query);
            }));
        }

        if ($missingOnly) {
            $languageCount = max(1, count($languages));
            $grouped = array_values(array_filter($grouped, static function (array $item) use ($languageCount): bool {
                return (int) ($item['languages_count'] ?? 0) < $languageCount || (bool) ($item['has_missing'] ?? false);
            }));
        }

        return $this->success('Dil liste sayfasi hazir.', [
            'path' => $path,
            'filters' => ['q' => (string) ($filters['q'] ?? ''), 'missing' => $missingOnly ? '1' : '0'],
            'languages' => $languages,
            'translation_groups' => $grouped,
            'flash_message' => (string) $this->session->getFlash('translations.success', ''),
            'error_message' => (string) $this->session->getFlash('translations.error', ''),
        ]);
    }

    /**
     * @param string $path
     * @param int $id
     * @return ServiceResult
     */
    public function editForm(string $path, int $id): ServiceResult
    {
        $guard = $this->guard();
        if (! $guard->isSuccess()) {
            return $guard;
        }

        $row = Models::get('language_translations')->where('id', $id)->first();
        if (! is_object($row)) {
            return $this->redirectError('Ceviri kaydi bulunamadi.', '/admin/languages', 404);
        }

        $key = (string) ($row->translation_key ?? '');
        $languages = $this->languageRows();
        $translations = $this->translationsForKey($key);
        $fields = [];

        foreach ($languages as $language) {
            $languageId = (int) ($language['id'] ?? 0);
            $fieldKey = 'translations.' . $languageId;
            $fields[] = [
                'id' => $languageId,
                'code' => (string) ($language['code'] ?? ''),
                'name' => (string) ($language['name'] ?? ''),
                'value' => (string) $this->session->getFlash($fieldKey, $translations[$languageId]['translation_value'] ?? ''),
            ];
        }

        return $this->success('Ceviri duzenleme formu hazir.', [
            'path' => $path,
            'csrf_token' => $this->csrf->token(),
            'translation_key' => $key,
            'translation_fields' => $fields,
            'error_message' => (string) $this->session->getFlash('translations.error', ''),
            'form_action' => '/admin/languages/update/' . $id,
        ]);
    }

    /**
     * @param int $id
     * @param array<string, mixed> $payload
     * @return ServiceResult
     */
    public function update(int $id, array $payload): ServiceResult
    {
        $guard = $this->guard();
        if (! $guard->isSuccess()) {
            return $guard;
        }

        $row = Models::get('language_translations')->where('id', $id)->first();
        if (! is_object($row)) {
            return $this->redirectError('Ceviri kaydi bulunamadi.', '/admin/languages', 404);
        }

        $key = (string) ($row->translation_key ?? '');
        $translations = $payload['translations'] ?? null;

        if (! is_array($translations)) {
            $this->session->flash('translations.error', 'Ceviri alanlari gecerli degil.');

            return $this->redirectError('Ceviri alanlari gecerli degil.', '/admin/languages/edit/' . $id, 422);
        }

        foreach ($translations as $languageId => $value) {
            $languageId = (int) $languageId;
            $text = trim((string) $value);
            $this->session->flash('translations.' . $languageId, $text);

            $existing = Models::get('language_translations')
                ->where('language_id', $languageId)
                ->where('translation_key', $key)
                ->first();

            if (is_object($existing)) {
                $existing->translation_value = $text;
                $existing->update();
                continue;
            }

            $translation = Models::get('language_translations');
            $translation->language_id = $languageId;
            $translation->translation_key = $key;
            $translation->translation_value = $text;
            $translation->save();
        }

        $this->session->flash('translations.success', 'Ceviriler guncellendi.');

        return $this->redirectSuccess('Ceviriler guncellendi.', '/admin/languages');
    }

    /**
     * @return ServiceResult
     */
    private function guard(): ServiceResult
    {
        if ($this->auth->checkAdmin()) {
            return $this->success('Admin oturumu gecerli.');
        }

        return $this->redirectError('Admin oturumu bulunamadi.', '/admin/login', 401);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function languageRows(): array
    {
        return $this->normalizeRows(
            Models::get('languages')
                ->orderBy('id', 'ASC')
                ->all()
        );
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function translationRows(): array
    {
        return $this->normalizeRows(
            Models::get('language_translations')
                ->orderBy('translation_key', 'ASC')
                ->orderBy('language_id', 'ASC')
                ->all()
        );
    }

    /**
     * @param string $key
     * @return array<int, array<string, mixed>>
     */
    private function translationsForKey(string $key): array
    {
        $rows = $this->normalizeRows(
            Models::get('language_translations')
                ->where('translation_key', $key)
                ->all()
        );
        $byLanguage = [];

        foreach ($rows as $row) {
            $byLanguage[(int) ($row['language_id'] ?? 0)] = $row;
        }

        return $byLanguage;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array<string, mixed>>
     */
    private function groupTranslationsByKey(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $key = (string) ($row['translation_key'] ?? '');
            if ($key === '') {
                continue;
            }

            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'id' => (int) ($row['id'] ?? 0),
                    'translation_key' => $key,
                    'preview' => (string) ($row['translation_value'] ?? ''),
                    'languages_count' => 0,
                    'has_missing' => false,
                ];
            }

            $grouped[$key]['languages_count']++;

            if (trim((string) ($row['translation_value'] ?? '')) === '') {
                $grouped[$key]['has_missing'] = true;
            }
        }

        return array_values($grouped);
    }

    /**
     * @param object|array|false $rows
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
            if (is_object($item)) {
                $normalized[] = get_object_vars($item);
            }
        }

        return $normalized;
    }
}
