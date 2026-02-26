<?php

namespace App\Domain\Videos\Requests;

use App\Domain\Videos\Models\Input;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CancelInputPredictionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = auth('api')->id();

        return [
            'input_id' => [
                'required',
                'integer',
                Rule::exists('inputs', 'id')
                    ->where('user_id', (int) $userId)
                    ->whereIn('status', [
                        Input::CREATED,
                        Input::PROCESSING,
                    ]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'input_id.required' => __('validation.custom.input_id.required'),
            'input_id.integer' => __('validation.custom.input_id.integer'),
            'input_id.exists' => __('validation.custom.input_id.exists'),
        ];
    }
}
