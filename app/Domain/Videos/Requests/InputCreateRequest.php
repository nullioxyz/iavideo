<?php

namespace App\Domain\Videos\Requests;

use App\Domain\Videos\Rules\AspectRatio;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InputCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'preset_id' => [
                'required',
                'integer',
                Rule::exists('presets', 'id')->where('active', true),
            ],

            'image' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:8192',
                'dimensions:min_width=256,min_height=256,max_width=4096,max_height=4096',

                new AspectRatio(9, 16, tolerancePercent: 3.0),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('preset_id')) {
            $this->merge([
                'preset_id' => (int) $this->input('preset_id'),
            ]);
        }
    }

    public function messages(): array
    {
        return [
            'preset_id.required' => __('validation.custom.preset_id.required'),
            'preset_id.integer'  => __('validation.custom.preset_id.integer'),
            'preset_id.exists'   => __('validation.custom.preset_id.exists'),

            'image.required'     => __('validation.custom.image.required'),
            'image.file'         => __('validation.custom.image.file'),
            'image.image'        => __('validation.custom.image.image'),
            'image.mimes'        => __('validation.custom.image.mimes'),
            'image.max'          => __('validation.custom.image.max'),
            'image.dimensions'   => __('validation.custom.image.dimensions'),
        ];
    }
}
