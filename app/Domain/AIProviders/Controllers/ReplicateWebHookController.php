<?php

namespace App\Domain\AIProviders\Controllers;

use App\Domain\AIProviders\Requests\ReplicateWebhookRequest;
use App\Domain\Videos\DTO\PredictionWebhookDTO;
use App\Domain\Videos\UseCases\ReceivePredictionWebhookUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ReplicateWebHookController extends Controller
{
    public function __construct(
        public readonly ReceivePredictionWebhookUseCase $useCase
    ) {}

    public function __invoke(ReplicateWebhookRequest $request): Response
    {
        $externalId = (string) $request->input('id');
        $status = (string) $request->input('status');

        try {
            Log::info('replicate.webhook.received', [
                'external_id' => $externalId,
                'status' => $status,
            ]);

            $this->useCase->execute(
                PredictionWebhookDTO::fromArray($request->all())
            );

            Log::info('replicate.webhook.processed', [
                'external_id' => $externalId,
                'status' => $status,
            ]);

            return response()->noContent();
        } catch (\Exception $e) {
            Log::error('replicate.webhook.failed', [
                'external_id' => $externalId,
                'status' => $status,
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => [
                    'code' => 'internal_error',
                    'message' => 'Internal Server Error',
                ],
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
