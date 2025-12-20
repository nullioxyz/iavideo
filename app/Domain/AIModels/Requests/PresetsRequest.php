<?php

namespace App\Domain\AIModels\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PresetsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
