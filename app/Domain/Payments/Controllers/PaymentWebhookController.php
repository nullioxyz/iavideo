<?php

namespace App\Domain\Payments\Controllers;

use App\Domain\Payments\DTO\WebhookPayloadDTO;
use App\Domain\Payments\Requests\PaymentWebhookRequest;
use App\Domain\Payments\UseCases\HandlePaymentWebhookUseCase;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class PaymentWebhookController extends Controller
{
    public function __construct(private readonly HandlePaymentWebhookUseCase $useCase) {}

    public function __invoke(string $provider, PaymentWebhookRequest $request): Response
    {
        $this->useCase->execute(
            WebhookPayloadDTO::fromArray($provider, $request->validated())
        );

        return response()->noContent();
    }
}
