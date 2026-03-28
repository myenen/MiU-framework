<?php

declare(strict_types=1);

namespace App\Requests\Site;

use Core\Validation\FormRequest;

/**
 * Site login formu dogrulama kurallarini tanimlar.
 */
final class SiteLoginRequest extends FormRequest
{
    /**
     * @return array<string, string>
     */
    protected function rules(): array
    {
        return [
            'email' => 'required|email|max:120',
            'password' => 'required|min:6|max:120',
        ];
    }
}
