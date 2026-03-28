<?php

declare(strict_types=1);

namespace Core\Validation;

/**
 * Uygulama katmaninda tekrar kullanilabilir form dogrulama nesneleri icin temel sinif.
 */
abstract class FormRequest
{
    /**
     * @param Validator $validator Kural tabanli dogrulama servisi.
     */
    public function __construct(
        private readonly Validator $validator
    ) {
    }

    /**
     * Istege ait dogrulama kurallarini dondurur.
     *
     * @return array<string, string|array<int, string>>
     */
    abstract protected function rules(): array;

    /**
     * Veriyi kurallara gore dogrular.
     *
     * @param array<string, mixed> $data Form veya istek verisi.
     * @return ValidationResult
     */
    public function validate(array $data): ValidationResult
    {
        return $this->validator->validate($data, $this->rules());
    }
}
