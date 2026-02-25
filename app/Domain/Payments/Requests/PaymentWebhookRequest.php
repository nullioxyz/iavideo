<?php

namespace App\Domain\Payments\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PaymentWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_id' => ['required', 'string', 'max:255'],
            'external_id' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'max:50'],
            'failure_code' => ['nullable', 'string', 'max:255'],
            'failure_message' => ['nullable', 'string'],
        ];
    }

    protected function passedValidation(): void
    {
        $secret = (string) config('services.payments.webhook_secret', '');
        if ($secret === '') {
            return;
        }

        $header = (string) $this->header('X-Payment-Signature', '');
        $expected = hash_hmac('sha256', (string) $this->getContent(), $secret);

        if (! hash_equals($expected, $header)) {
            throw new HttpException(401, 'Unauthorized payment webhook');
        }
    }
}
