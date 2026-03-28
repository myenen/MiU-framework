<?php

declare(strict_types=1);

namespace Core\Validation;

/**
 * Form ve istek verileri icin hafif kural tabanli dogrulayici.
 */
final class Validator
{
    /**
     * Girdi dizisini basit metin kurallariyla dogrular.
     *
     * @param array $data Girdi verisi.
     * @param array $rules Alanlara gore gruplanmis dogrulama kurallari.
     * @return ValidationResult
     */
    public function validate(array $data, array $rules): ValidationResult
    {
        $errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;
            $parts = is_array($fieldRules) ? $fieldRules : explode('|', $fieldRules);

            foreach ($parts as $rule) {
                [$name, $parameter] = array_pad(explode(':', (string) $rule, 2), 2, null);

                if ($name === 'nullable' && ($value === null || trim((string) $value) === '')) {
                    break;
                }

                if ($name === 'required' && ($value === null || trim((string) $value) === '')) {
                    $errors[$field][] = 'Bu alan zorunludur.';
                }

                if ($name === 'email' && $value !== null && trim((string) $value) !== '' && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = 'Gecerli bir e-posta giriniz.';
                }

                if ($name === 'min' && $value !== null && mb_strlen((string) $value) < (int) $parameter) {
                    $errors[$field][] = sprintf('En az %d karakter olmali.', (int) $parameter);
                }

                if ($name === 'max' && $value !== null && mb_strlen((string) $value) > (int) $parameter) {
                    $errors[$field][] = sprintf('En fazla %d karakter olmali.', (int) $parameter);
                }

                if ($name === 'numeric' && $value !== null && trim((string) $value) !== '' && ! is_numeric($value)) {
                    $errors[$field][] = 'Sayisal bir deger giriniz.';
                }

                if ($name === 'in' && $value !== null && trim((string) $value) !== '') {
                    $allowed = array_map('trim', explode(',', (string) $parameter));

                    if (! in_array((string) $value, $allowed, true)) {
                        $errors[$field][] = 'Gecerli bir deger seciniz.';
                    }
                }
            }
        }

        return new ValidationResult($errors);
    }
}
