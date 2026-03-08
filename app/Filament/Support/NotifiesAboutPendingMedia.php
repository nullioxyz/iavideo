<?php

namespace App\Filament\Support;

use Filament\Notifications\Notification;

trait NotifiesAboutPendingMedia
{
    protected function notifyPendingMedia(int $count = 1): void
    {
        Notification::make()
            ->title(__('filament.notifications.pending_media.title'))
            ->body(trans_choice('filament.notifications.pending_media.body', $count, ['count' => $count]))
            ->warning()
            ->send();
    }
}
