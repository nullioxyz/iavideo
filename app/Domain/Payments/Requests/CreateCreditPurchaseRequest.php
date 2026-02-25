<?php

namespace App\Domain\Payments\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateCreditPurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'credits' => ['required', 'integer', 'min:1', 'max:10000'],
        ];
    }
}
