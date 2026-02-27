<?php

namespace App\Domain\Videos\Listeners;

use App\Domain\Videos\Events\CreatePredictionForInput;
use App\Domain\Videos\Events\InputCreated;
use App\Domain\Videos\Models\Input;
use App\Infra\Storage\InputImageStorageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UploadInputImageListener implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'uploads';

    public function handle(InputCreated $event, InputImageStorageService $imageStorage): void
    {
        /** @var Input $input */
        $input = Input::query()->findOrFail($event->inputId);

        if (! $imageStorage->tempFileExists($event->tempPath)) {
            $input->update(['status' => 'failed']);

            return;
        }

        $input->update(['status' => 'processing']);
        $media = $imageStorage->attachFromTemp($input, $event->tempPath);

        $input->update([
            'start_image_path' => $media->getPathRelativeToRoot(),
        ]);

        CreatePredictionForInput::dispatch($input->getKey());
    }
}
