<?php

declare(strict_types=1);

namespace App\Requests\Api;

use Core\Validation\FormRequest;

/**
 * API kullanici olusturma istegi icin dogrulama kurallarini tanimlar.
 */
final class UserCreateRequest extends FormRequest
{
    /**
     * @return array<string, string>
     */
    protected function rules(): array
    {
        return [
            'name' => 'required|min:2|max:120',
            'surname' => 'required|min:2|max:120',
            'email' => 'required|email|max:191',
            'password' => 'required|min:6|max:120',
            'role' => 'required|numeric',
            'status' => 'required|in:active,passive',
            'phone' => 'nullable|max:30',
            'city' => 'nullable|max:100',
            'address' => 'nullable|max:500',
        ];
    }
}
