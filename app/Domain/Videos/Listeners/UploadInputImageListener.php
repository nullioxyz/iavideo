<?php

namespace App\Domain\Videos\Listeners;

use App\Domain\Videos\Events\CreatePredictionForInput;
use App\Domain\Videos\Events\InputCreated;
use App\Domain\Videos\Models\Input;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;

class UploadInputImageListener implements ShouldQueue
{
    use InteractsWithQueue;

    public $queue = 'uploads';

    public function handle(InputCreated $event): void
    {
        /** @var Input $input */
        $input = Input::query()->findOrFail($event->inputId);

        $absolutePath = Storage::disk('local')->path($event->tempPath);

        if (! file_exists($absolutePath)) {
            $input->update(['status' => 'failed']);

            return;
        }

        $input->update(['status' => 'processing']);

        $media = $input
            ->addMedia($absolutePath)
            ->usingName('start_image')
            ->usingFileName(basename($absolutePath))
            ->toMediaCollection('start_image');

        $input->update([
            'start_image_path' => $media->getPath(),
        ]);

        Storage::disk('local')->delete($event->tempPath);

        CreatePredictionForInput::dispatch($input->getKey());
    }
}
