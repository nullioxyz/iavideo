<?php

namespace App\Domain\AIProviders\Infra\Replicate\Fake;

use App\Domain\AIProviders\Contracts\ProviderClientInterface;
use App\Domain\AIProviders\DTO\ProviderCreateResultDTO;
use App\Domain\AIProviders\DTO\ProviderGetResultDTO;
use Symfony\Component\HttpFoundation\Response;

final class FakeReplicateClient implements ProviderClientInterface
{
    public function providerSlug(): string
    {
        return 'replicate';
    }

    public function create(string $modelSlug, array $payload, array $headers = []): ProviderCreateResultDTO
    {
        return new ProviderCreateResultDTO(
            '2wbzrawha9rmw0cv9h5ajeyyn4',
            Response::HTTP_CREATED,
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
            statusCode: 200,
            payload: [
                'completed_at' => '2025-12-28T17:07:02.050986Z',
                'created_at' => '2025-12-28T17:04:51.439000Z',
                'data_removed' => true,
                'error' => null,
                'id' => 'rqgf4j40xxrmt0cvcqnrf0329m',
                'input' => [],
                'metrics' => [
                    'predict_time' => 130.600248305,
                    'total_time' => 130.611986008,
                ],
                'model' => 'kwaivgi/kling-v2.5-turbo-pro',
                'output' => 'https://cdn.replicate.com/fake/video.mp4',
                'source' => 'api',
                'started_at' => '2025-12-28T17:04:51.450737Z',
                'status' => 'succeeded',
                'urls' => [
                    'stream' => 'https://stream.replicate.com/v1/files/jbxs-c4b5dqladlrvzlybu5awscrjpskhciw2fhrpn3tdl35v76ogjpxq',
                    'get' => 'https://api.replicate.com/v1/predictions/rqgf4j40xxrmt0cvcqnrf0329m',
                    'cancel' => 'https://api.replicate.com/v1/predictions/rqgf4j40xxrmt0cvcqnrf0329m/cancel',
                    'web' => 'https://replicate.com/p/rqgf4j40xxrmt0cvcqnrf0329m',
                ],
                'version' => 'hidden',
            ]
        );
    }
}
