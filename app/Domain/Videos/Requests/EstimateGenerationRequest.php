<?php

namespace App\Domain\Videos\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EstimateGenerationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'model_id' => [
                'required',
                'integer',
                Rule::exists('models', 'id'),
            ],
            'preset_id' => [
                'required',
                'integer',
                Rule::exists('presets', 'id')->where('active', true),
            ],
            'duration_seconds' => [
                'nullable',
                'integer',
                'min:1',
                'max:300',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['model_id', 'preset_id', 'duration_seconds'] as $field) {
            if ($this->has($field)) {
                $this->merge([
                    $field => (int) $this->input($field),
                ]);
            }
        }
    }
}
