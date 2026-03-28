<?php

declare(strict_types=1);

namespace App\Requests\Admin;

use Core\Validation\FormRequest;

/**
 * Admin rol guncelleme formu icin dogrulama kurallarini tanimlar.
 */
final class RoleUpdateRequest extends FormRequest
{
    /**
     * @return array<string, string>
     */
    protected function rules(): array
    {
        return [
            'name' => 'required|min:2|max:100',
        ];
    }
}
