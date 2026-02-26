<?php

namespace App\Domain\Videos\UseCases;

use App\Domain\Auth\Models\User;
use App\Domain\Settings\Models\Setting;
use App\Domain\Credits\UseCases\ReserveCreditUseCase;
use App\Domain\Broadcasting\Events\UserJobUpdatedBroadcast;
use App\Domain\Videos\Contracts\Repositories\InputRepositoryInterface;
use App\Domain\Videos\DTO\InputCreateDTO;
use App\Domain\Videos\Events\InputCreated;
use App\Domain\Videos\Models\Input;
use App\Infra\Contracts\InputImageIngestionInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class CreateInputUseCase
{
    public function __construct(
        private readonly InputRepositoryInterface $inputRepository,
        private readonly InputImageIngestionInterface $ingestion,
        private readonly ReserveCreditUseCase $reserveCreditUseCase,

    ) {}

    public function execute(User $user, InputCreateDTO $dto, UploadedFile $file): Input
    {
        /** @var Input $input */
        $input = DB::transaction(function () use ($user, $dto): Input {
            /** @var User|null $lockedUser */
            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->first();

            if (! $lockedUser instanceof User) {
                throw new \RuntimeException('User not found.');
            }

            $this->assertDailyInputsLimit($lockedUser);

            $input = $this->inputRepository->create(
                $dto->toArray($lockedUser->getKey())
            );

            $this->reserveCreditUseCase->execute($lockedUser, [
                'reason' => 'Charge for input creation',
                'reference_type' => 'input_creation',
                'reference_id' => $input->getKey(),
            ]);

            $input->update([
                'credit_debited' => true,
            ]);

            return $input->refresh();
        });

        $tempPath = $this->ingestion->ingest($input->getKey(), $file);
        InputCreated::dispatch($input->getKey(), $tempPath);
        event(UserJobUpdatedBroadcast::fromInput($input->refresh()));

        Log::info('videos.input.created', [
            'input_id' => $input->getKey(),
            'user_id' => $user->getKey(),
            'preset_id' => $dto->presetId,
        ]);

        return $input;
    }

    private function assertDailyInputsLimit(User $user): void
    {
        $maxDailyInputs = $this->settingInt('max_daily_inputs', 50);

        $todayInputsCount = Input::query()
            ->where('user_id', $user->getKey())
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($todayInputsCount >= $maxDailyInputs) {
            throw new \DomainException('Daily input generation limit exceeded.');
        }
    }

    private function settingInt(string $key, int $default): int
    {
        $value = Setting::query()->where('key', $key)->value('value');

        return is_numeric($value) ? (int) $value : $default;
    }
}
