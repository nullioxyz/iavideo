<?php

namespace App\Domain\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language_id' => ['nullable', 'integer', 'exists:languages,id'],
            'theme_preference' => ['nullable', 'string', 'in:light,dark,system'],
        ];
    }
}
