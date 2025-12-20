<?php

namespace App\Domain\AIModels\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IAModelsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
}
