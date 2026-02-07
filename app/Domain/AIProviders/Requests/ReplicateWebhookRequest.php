<?php

namespace App\Domain\AIProviders\Requests;

use App\Domain\Videos\Models\Prediction;
use Illuminate\Foundation\Http\FormRequest;

class ReplicateWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->predictionExists();
    }

    public function predictionExists(): bool
    {
        return Prediction::where('external_id', $this->get('id'))->exists();
    }

    public function rules(): array
    {
        return [
            'id' => 'required',
            'version' => 'required',
            'status' => 'required',
        ];
    }
}
