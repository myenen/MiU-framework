<?php

declare(strict_types=1);

namespace Core\Validation;

/**
 * Bir dogrulama calismasinin sonucunu temsil eder.
 */
final class ValidationResult
{
    /**
     * @param array $errors Alan adina gore gruplanmis dogrulama hatalari.
     */
    public function __construct(
        private readonly array $errors = []
    ) {
    }

    /**
     * Dogrulamanin basarisiz olup olmadigini belirtir.
     *
     * @return bool
     */
    public function fails(): bool
    {
        return $this->errors !== [];
    }

    /**
     * Tum dogrulama hatalarini dondurur.
     *
     * @return array
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * Verilen alan icin ilk dogrulama hatasini dondurur.
     *
     * @param string $field Alan adi.
     * @return string
     */
    public function first(string $field): string
    {
        return (string) ($this->errors[$field][0] ?? '');
    }

    /**
     * Verilen alan listesinde bulunan ilk dogrulama hatasini dondurur.
     *
     * @param array<int, string> $fields Alan adlari.
     * @return string
     */
    public function firstOf(array $fields): string
    {
        foreach ($fields as $field) {
            $message = $this->first($field);

            if ($message !== '') {
                return $message;
            }
        }

        return '';
    }
}
