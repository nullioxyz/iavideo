<?php

namespace App\Domain\Videos\UseCases\Tests;

use App\Domain\AIModels\Contracts\Adapters\ModelAdapterRegistryInterface as AdaptersModelAdapterRegistryInterface;
use App\Domain\AIModels\Contracts\Infra\VideoModelAdapterInterface as InfraVideoModelAdapterInterface;
use App\Domain\AIModels\Infra\Replicate\KlingV25TurboProAdapter as ReplicateKlingV25TurboProAdapter;
use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\AIProviders\Contracts\ProviderClientInterface;
use App\Domain\AIProviders\DTO\ProviderCreateResultDTO;
use App\Domain\AIProviders\DTO\ProviderGetResultDTO;
use App\Domain\Auth\Models\User;
use App\Domain\Platforms\Models\Platform as ModelsPlatform;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\Prediction;
use App\Domain\Videos\UseCases\GetPredictionUseCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class GetPredictionUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_get_prediction_via_replicate(): void
    {
        Config::set('services.replicate.token', 'test-token');

        $user = User::factory()->create();
        // Http fake simulando o curl do Replicate (endpoint por model + Prefer: wait)
        Http::fake(function ($request) {
            $this->assertSame(
                'https://api.replicate.com/v1/predictions/2wbzrawha9rmw0cv9h5ajeyyn4',
                (string) $request->url()
            );

            $this->assertSame('Bearer test-token', $request->header('Authorization')[0] ?? null);
            $this->assertSame('application/json', $request->header('Accept')[0] ?? null);

            return Http::response(
                [
                    'id' => '2wbzrawha9rmw0cv9h5ajeyyn4',
                    'model' => 'kwaivgi/kling-v2.5-turbo-pro',
                    'version' => 'hidden',
                    'input' => [],
                    'logs' => '',
                    'output' => null,
                    'data_removed' => true,
                    'error' => null,
                    'source' => 'api',
                    'status' => 'succeeded',
                    'created_at' => '2025-12-23T17:38:33.938Z',
                    'started_at' => '2025-12-23T17:38:33.972135Z',
                    'completed_at' => '2025-12-23T17:40:50.521287Z',
                    'urls' => [
                        'cancel' => 'https://api.replicate.com/v1/predictions/2wbzrawha9rmw0cv9h5ajeyyn4/cancel',
                        'get' => 'https://api.replicate.com/v1/predictions/2wbzrawha9rmw0cv9h5ajeyyn4',
                        'stream' => 'https://stream.replicate.com/v1/files/jbxs-znpew3x3sep5lghqeu5bogxmlkilpxxchyri73edm6bgqg72p3wq',
                        'web' => 'https://replicate.com/p/2wbzrawha9rmw0cv9h5ajeyyn4',
                    ],
                    'metrics' => [
                        'predict_time' => 136.549151731,
                        'total_time' => 136.5832877,
                    ],
                ], 201);
        });

        $platform = ModelsPlatform::query()->create([
            'name' => 'Replicate',
            'slug' => 'replicate',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $model = AIModel::query()->create([
            'platform_id' => $platform->id,
            'name' => 'Kling v2.5 Turbo Pro',
            'slug' => 'kwaivgi/kling-v2.5-turbo-pro',
            'version' => null,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $preset = Preset::query()->create([
            'name' => 'Preset 9:16',
            'prompt' => 'Go until to the start of the universe. Go to the Big Bang.',
            'negative_prompt' => null,
            'aspect_ratio' => '9:16',
            'duration_seconds' => 5,
            'default_model_id' => $model->id,
            'cost_estimate_usd' => 0.3500,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $input = Input::query()->create([
            'user_id' => $user->getKey(),
            'preset_id' => $preset->id,
            'start_image_path' => null,
            'original_filename' => 'tattoo.png',
            'mime_type' => 'image/png',
            'size_bytes' => 12345,
            'credit_debited' => false,
            'status' => 'created',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $prediction = Prediction::factory()->create([
            'input_id' => $input->getKey(),
            'model_id' => $model->getKey(),
            'external_id' => '2wbzrawha9rmw0cv9h5ajeyyn4',
            'status' => 'starting',
            'source' => 'web',
            'attempt' => 1,
            'queued_at' => Carbon::now(),
        ]);

        $this->app->singleton(ProviderClientInterface::class, function ($app) {
            $replicate = new class implements ProviderClientInterface
            {
                public function providerSlug(): string
                {
                    return 'replicate';
                }

                public function create(string $modelSlug, array $payload, array $headers = []): ProviderCreateResultDTO
                {
                    return new ProviderCreateResultDTO(
                        '2wbzrawha9rmw0cv9h5ajeyyn4',
                        'starting',
                        [
                            'id' => '2wbzrawha9rmw0cv9h5ajeyyn4',
                            'model' => 'kwaivgi/kling-v2.5-turbo-pro',
                            'version' => 'hidden',
                            'input' => [
                                'image' => 'https://images.unsplash.com/photo-1758567088839-15860fb2a081',
                                'prompt' => 'Go until to the start of the universe. Go to the Big Bang.',
                            ],
                            'logs' => '',
                            'output' => null,
                            'data_removed' => false,
                            'error' => null,
                            'source' => 'api',
                            'status' => 'starting',
                            'created_at' => '2025-12-23T17:38:33.938Z',
                            'urls' => [
                                'cancel' => 'https://api.replicate.com/v1/predictions/2wbzrawha9rmw0cv9h5ajeyyn4/cancel',
                                'get' => 'https://api.replicate.com/v1/predictions/2wbzrawha9rmw0cv9h5ajeyyn4',
                                'stream' => 'https://stream.replicate.com/v1/files/jbxs-znpew3x3sep5lghqeu5bogxmlkilpxxchyri73edm6bgqg72p3wq',
                                'web' => 'https://replicate.com/p/2wbzrawha9rmw0cv9h5ajeyyn4',
                            ],
                        ]

                    );
                }

                public function get(string $externalId): ProviderGetResultDTO
                {
                    return new ProviderGetResultDTO(
                        Response::HTTP_OK,
                        [
                            'id' => '2wbzrawha9rmw0cv9h5ajeyyn4',
                            'model' => 'kwaivgi/kling-v2.5-turbo-pro',
                            'version' => 'hidden',
                            'input' => [],
                            'logs' => '',
                            'output' => null,
                            'data_removed' => true,
                            'error' => null,
                            'source' => 'api',
                            'status' => 'succeeded',
                            'created_at' => '2025-12-23T17:38:33.938Z',
                            'started_at' => '2025-12-23T17:38:33.972135Z',
                            'completed_at' => '2025-12-23T17:40:50.521287Z',
                            'urls' => [
                                'cancel' => 'https://api.replicate.com/v1/predictions/2wbzrawha9rmw0cv9h5ajeyyn4/cancel',
                                'get' => 'https://api.replicate.com/v1/predictions/2wbzrawha9rmw0cv9h5ajeyyn4',
                                'stream' => 'https://stream.replicate.com/v1/files/jbxs-znpew3x3sep5lghqeu5bogxmlkilpxxchyri73edm6bgqg72p3wq',
                                'web' => 'https://replicate.com/p/2wbzrawha9rmw0cv9h5ajeyyn4',
                            ],
                            'metrics' => [
                                'predict_time' => 136.549151731,
                                'total_time' => 136.5832877,
                            ],
                        ]
                    );
                }

                public function cancel(string $externalId): ProviderGetResultDTO
                {
                    throw new \LogicException('not needed');
                }
            };

            return $replicate;
        });

        $this->app->singleton(AdaptersModelAdapterRegistryInterface::class, function () {
            return new class implements AdaptersModelAdapterRegistryInterface
            {
                public function video(string $providerSlug, string $modelSlug): InfraVideoModelAdapterInterface
                {
                    $adapter = new ReplicateKlingV25TurboProAdapter;

                    if ($providerSlug !== $adapter->providerSlug() || $modelSlug !== $adapter->modelSlug()) {
                        throw new \InvalidArgumentException("Adapter not found for {$providerSlug}:{$modelSlug}");
                    }

                    return $adapter;
                }
            };
        });

        /** @var GetPredictionUseCase $useCase */
        $useCase = $this->app->make(GetPredictionUseCase::class);

        $prediction = $useCase->execute($prediction);

        $this->assertInstanceOf(Prediction::class, $prediction);
        $this->assertSame('2wbzrawha9rmw0cv9h5ajeyyn4', $prediction->external_id);
        $this->assertSame('succeeded', $prediction->status);
        $this->assertSame($input->id, $prediction->input_id);
        $this->assertSame($model->id, $prediction->model_id);

        $this->assertDatabaseHas('predictions', [
            'id' => $prediction->id,
            'external_id' => '2wbzrawha9rmw0cv9h5ajeyyn4',
            'status' => 'succeeded',
        ]);
    }
}
