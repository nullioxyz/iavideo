<?php

namespace App\Domain\AIProviders\Controllers;

use App\Domain\AIProviders\Requests\ReplicateWebhookRequest;
use App\Domain\Videos\DTO\PredictionWebhookDTO;
use App\Domain\Videos\UseCases\ReceivePredictionWebhookUseCase;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class ReplicateWebHookController extends Controller
{
    public function __construct(
        public readonly ReceivePredictionWebhookUseCase $useCase
    )
    {}

    public function __invoke(ReplicateWebhookRequest $request): Response
    {
        $this->useCase->execute(
            PredictionWebhookDTO::fromArray($request->all())
        );

        return response()->noContent();
    }
}
