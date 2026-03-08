<?php

namespace App\Domain\AIProviders\Requests;

use App\Domain\Videos\Models\Prediction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ReplicateWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        $externalId = $this->input('id');

        if (! is_string($externalId) || $externalId === '') {
            return false;
        }

        return Prediction::query()
            ->where('external_id', $externalId)
            ->exists();
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'string',
            ],
            'version' => 'required',
            'status' => 'required',
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            response()->json([
                'error' => [
                    'code' => 'unauthorized_webhook',
                    'message' => 'Unauthorized webhook',
                ],
            ], 401)
        );
    }
}
