<?php

namespace App\Domain\Videos\Controllers;

use App\Domain\Videos\UseCases\DownloadUserJobVideoUseCase;
use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadJobVideoController extends Controller
{
    public function __construct(
        private readonly DownloadUserJobVideoUseCase $useCase,
    ) {}

    public function __invoke(int $job): BinaryFileResponse
    {
        $userId = (int) auth('api')->id();

        $result = $this->useCase->execute($userId, $job);

        /** @var \Spatie\MediaLibrary\MediaCollections\Models\Media $media */
        $media = $result['media'];

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
