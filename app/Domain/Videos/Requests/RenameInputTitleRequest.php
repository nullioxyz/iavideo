<?php

namespace App\Domain\Videos\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RenameInputTitleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('title')) {
            $title = trim((string) $this->input('title', ''));
            $this->merge([
                'title' => $title === '' ? null : $title,
            ]);
        }
    }
}
