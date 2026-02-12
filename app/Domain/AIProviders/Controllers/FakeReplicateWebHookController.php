<?php

namespace App\Domain\AIProviders\Controllers;

use App\Domain\AIProviders\Requests\ReplicateWebhookRequest;
use App\Domain\Videos\DTO\PredictionWebhookDTO;
use App\Domain\Videos\UseCases\ReceivePredictionWebhookUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class FakeReplicateWebHookController extends Controller
{
    public function __construct(
        public readonly ReceivePredictionWebhookUseCase $useCase
    ) {}

    public function __invoke(ReplicateWebhookRequest $request): Response
    {
        if (app()->environment(['production'])) {
            return response()->json([
                'error' => [
                    'code' => 'not_found',
                    'message' => 'Not Found',
                ],
            ], Response::HTTP_NOT_FOUND);
        }

        $externalId = (string) $request->input('id');
        $status = (string) $request->input('status');

        try {
            Log::info('replicate.webhook.fake.received', [
                'external_id' => $externalId,
                'status' => $status,
            ]);

            $this->useCase->execute(
                PredictionWebhookDTO::fromArray($request->all())
            );

            Log::info('replicate.webhook.fake.processed', [
                'external_id' => $externalId,
                'status' => $status,
            ]);

            return response()->noContent();
        } catch (\Exception $e) {
            Log::error('replicate.webhook.fake.failed', [
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
