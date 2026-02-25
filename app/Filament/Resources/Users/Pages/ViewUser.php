<?php

namespace App\Filament\Resources\Users\Pages;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\UseCases\CreateImpersonationLinkUseCase;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Validation\ValidationException;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open_as_platform_user')
                ->label('Abrir no frontend como usuário')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function (CreateImpersonationLinkUseCase $useCase): void {
                    $actor = auth('web')->user();
                    if (! $actor instanceof \App\Domain\Auth\Models\Admin) {
                        abort(401);
                    }

                    $actorUser = User::query()->findOrFail($actor->getKey());

                    /** @var \App\Domain\Auth\Models\Admin $record */
                    $record = $this->record;
                    $targetUser = User::query()->findOrFail($record->getKey());

                    try {
                        $hash = $useCase->execute($actorUser, $targetUser);
                    } catch (ValidationException $exception) {
                        Notification::make()
                            ->title('Não foi possível gerar hash de impersonação')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    $frontendUrl = rtrim((string) config('app.frontend_url'), '/');
                    $url = $frontendUrl.'/auth/impersonate?impersonation_hash='.$hash;

                    $this->redirect($url, navigate: false);
                }),
            EditAction::make(),
        ];
    }
}
