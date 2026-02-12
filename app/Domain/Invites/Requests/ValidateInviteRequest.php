<?php

namespace App\Domain\Invites\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateInviteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hash' => ['required', 'string'],
        ];
    }
}
