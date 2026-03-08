<?php

namespace App\Filament\Support;

use Filament\Notifications\Notification;

trait NotifiesAboutPendingMedia
{
    protected function notifyPendingMedia(int $count = 1): void
    {
        Notification::make()
            ->title('Upload em processamento')
            ->body($count === 1
                ? 'Seu arquivo foi salvo e está sendo processado em segundo plano. A mídia ficará disponível em breve.'
                : 'Seus arquivos foram salvos e estão sendo processados em segundo plano. As mídias ficarão disponíveis em breve.')
            ->warning()
            ->send();
    }
}
