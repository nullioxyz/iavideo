<?php

namespace App\Domain\AIProviders\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class ReplicateWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->passesWebhookSecret();
    }

    public function rules(): array
    {
        return [
            'id' => [
                'required',
                'string',
                Rule::exists('predictions', 'external_id'),
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

    private function passesWebhookSecret(): bool
    {
        $expected = (string) config('services.replicate.webhook_secret', '');
        $provided = (string) ($this->header('X-Replicate-Webhook-Secret')
            ?? $this->header('X-Webhook-Secret')
            ?? '');

        if ($expected === '') {
            return ! app()->environment('production');
        }

        return $provided !== '' && hash_equals($expected, $provided);
    }
}
