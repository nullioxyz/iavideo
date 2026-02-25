<?php

namespace App\Domain\Videos\UseCases;

use App\Domain\Videos\Contracts\Repositories\InputRepositoryInterface;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\PredictionOutput;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class DownloadUserJobVideoUseCase
{
    public function __construct(
        private readonly InputRepositoryInterface $repository,
    ) {}

    /**
     * @return array{media:Media,download_name:string,mime_type:string}
     */
    public function execute(int $userId, int $inputId): array
    {
        $input = $this->repository->findOwnedByIdWithRelations($userId, $inputId);

        if (! $input instanceof Input) {
            throw (new ModelNotFoundException())->setModel(Input::class, [$inputId]);
        }

        $prediction = $input->prediction;
        if (! $prediction) {
            throw (new ModelNotFoundException())->setModel(PredictionOutput::class, ['video']);
        }

        /** @var ?PredictionOutput $output */
        $output = $prediction->outputs
            ->where('kind', 'video')
            ->sortByDesc('id')
            ->first();

        if (! $output instanceof PredictionOutput) {
            throw (new ModelNotFoundException())->setModel(PredictionOutput::class, ['video']);
        }

        $media = $output->getMediaFile();
        if (! $media instanceof Media) {
            throw (new ModelNotFoundException())->setModel(PredictionOutput::class, ['video-media']);
        }

        $extension = pathinfo((string) $media->file_name, PATHINFO_EXTENSION);
        $extension = $extension !== '' ? strtolower($extension) : 'mp4';

        $baseName = trim((string) ($input->title ?? $input->original_filename ?? 'video'));
        $baseName = preg_replace('/[^A-Za-z0-9\-_]+/', '-', $baseName) ?: 'video';

        return [
            'media' => $media,
            'download_name' => $baseName.'.'.$extension,
            'mime_type' => (string) ($media->mime_type ?: $output->mime_type ?: 'application/octet-stream'),
        ];
    }
}
