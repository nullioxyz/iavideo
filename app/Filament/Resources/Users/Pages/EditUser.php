<?php

namespace App\Filament\Resources\Users\Pages;

use App\Domain\Auth\Models\Admin;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Support\RoleNames;
use App\Domain\Auth\UseCases\CreateImpersonationLinkUseCase;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['role_names']);

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var Admin $record */
        $record = $this->record;

        $user = User::query()->find($record->getKey());
        if ($user) {
            $user->syncRoles([RoleNames::PLATFORM_USER]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            Action::make('open_as_platform_user')
                ->label('Abrir no frontend como usuário')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function (CreateImpersonationLinkUseCase $useCase): void {
                    $actor = auth('web')->user();
                    if (! $actor instanceof Admin) {
                        abort(401);
                    }

                    $actorUser = User::query()->findOrFail($actor->getKey());
                    /** @var Admin $record */
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
            Action::make('reset_password')
                ->label('Resetar senha')
                ->icon('heroicon-o-key')
                ->color('warning')
                ->form([
                    TextInput::make('password')
                        ->label('Nova senha')
                        ->password()
                        ->revealable()
                        ->required()
                        ->minLength(8)
                        ->maxLength(72)
                        ->confirmed(),
                    TextInput::make('password_confirmation')
                        ->label('Confirmar nova senha')
                        ->password()
                        ->revealable()
                        ->required()
                        ->minLength(8)
                        ->maxLength(72),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'password' => (string) $data['password'],
                    ]);

                    Notification::make()
                        ->title('Senha redefinida')
                        ->success()
                        ->send();
                }),
            Action::make('force_first_login_password_reset')
                ->label('Ativar flag de reset')
                ->icon('heroicon-o-shield-exclamation')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->record->update([
                        'must_reset_password' => true,
                    ]);

                    Notification::make()
                        ->title('Flag de reset ativada')
                        ->body('O usuário precisará redefinir a senha no próximo login.')
                        ->success()
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
