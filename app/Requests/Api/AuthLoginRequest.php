<?php

declare(strict_types=1);

namespace App\Requests\Api;

use Core\Validation\FormRequest;

/**
 * API login istegi icin dogrulama kurallarini tanimlar.
 */
final class AuthLoginRequest extends FormRequest
{
    /**
     * @return array<string, string>
     */
    protected function rules(): array
    {
        return [
            'email' => 'required|email|max:120',
            'password' => 'required|min:6|max:120',
            'device_name' => 'nullable|min:2|max:120',
        ];
    }
}
