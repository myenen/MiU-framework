<?php

declare(strict_types=1);

namespace App\Requests\Admin;

use Core\Validation\FormRequest;

/**
 * Admin login formu dogrulama kurallarini tanimlar.
 */
final class AdminLoginRequest extends FormRequest
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
