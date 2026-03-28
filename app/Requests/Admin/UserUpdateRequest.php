<?php

declare(strict_types=1);

namespace App\Requests\Admin;

use Core\Validation\FormRequest;

/**
 * Kullanici guncelleme formu kurallarini tanimlar.
 */
final class UserUpdateRequest extends FormRequest
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
        ];
    }
}
