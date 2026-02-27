<?php

namespace App\Domain\Videos\Controllers;

use App\Domain\Observability\Support\StructuredActivityLogger;
use App\Domain\Auth\Models\User;
use App\Domain\Videos\UseCases\DownloadUserJobVideoUseCase;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadJobVideoController extends Controller
{
    public function __construct(
        private readonly DownloadUserJobVideoUseCase $useCase,
        private readonly StructuredActivityLogger $activityLogger,
    ) {}

    public function __invoke(int $job): BinaryFileResponse
    {
        $userId = (int) auth('api')->id();

        $result = $this->useCase->execute($userId, $job);

        /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media $media */
        $media = $result['media'];
        $user = auth('api')->user();
        $this->activityLogger->log(
            'video_downloaded',
            $user instanceof User ? $user : null,
            [
                'input_id' => $job,
                'download_name' => $result['download_name'],
            ]
        );

        return response()->download(
            file: $media->getPath(),
            name: $result['download_name'],
            headers: [
                'Content-Type' => $result['mime_type'],
                'Cache-Control' => 'private, max-age=0, must-revalidate',
            ],
        );
    }
}
