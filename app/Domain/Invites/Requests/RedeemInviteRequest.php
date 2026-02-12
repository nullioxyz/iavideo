<?php

namespace App\Domain\Invites\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RedeemInviteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
        ];
    }
}
