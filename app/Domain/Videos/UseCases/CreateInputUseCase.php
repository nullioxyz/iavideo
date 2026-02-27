<?php

namespace App\Domain\Videos\UseCases;

use App\Domain\Auth\Models\User;
use App\Domain\Broadcasting\Events\UserGenerationLimitAlertBroadcast;
use App\Domain\Broadcasting\Events\UserJobUpdatedBroadcast;
use App\Domain\Credits\UseCases\ReserveCreditUseCase;
use App\Domain\Observability\Support\StructuredActivityLogger;
use App\Domain\Settings\Models\Setting;
use App\Domain\Videos\Contracts\Repositories\InputRepositoryInterface;
use App\Domain\Videos\DTO\InputCreateDTO;
use App\Domain\Videos\Events\InputCreated;
use App\Domain\Videos\Models\Input;
use App\Infra\Contracts\InputImageIngestionInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

final class CreateInputUseCase
{
    public function __construct(
        private readonly InputRepositoryInterface $inputRepository,
        private readonly InputImageIngestionInterface $ingestion,
        private readonly ReserveCreditUseCase $reserveCreditUseCase,
        private readonly StructuredActivityLogger $activityLogger,
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
        $this->broadcastQuotaState($user, true);
        $this->activityLogger->log('input_created', $user, [
            'input_id' => $input->getKey(),
            'preset_id' => $dto->presetId,
        ]);

        return $input;
    }

    private function assertDailyInputsLimit(User $user): void
    {
        $quota = $this->quotaState($user);
        if ($quota['limit_reached']) {
            $this->broadcastQuotaState($user, true);
            $this->activityLogger->log('daily_generation_limit_reached', $user, $quota);
            throw new \DomainException('Daily input generation limit exceeded.');
        }
    }

    private function broadcastQuotaState(User $user, bool $onlyWhenNear = false): void
    {
        $quota = $this->quotaState($user);
        if ($onlyWhenNear && ! $quota['near_limit'] && ! $quota['limit_reached']) {
            return;
        }

        event(new UserGenerationLimitAlertBroadcast(
            userId: (int) $user->getKey(),
            dailyLimit: $quota['daily_limit'],
            usedToday: $quota['used_today'],
            remainingToday: $quota['remaining_today'],
            nearLimit: $quota['near_limit'],
            limitReached: $quota['limit_reached'],
        ));
    }

    /**
     * @return array{daily_limit:int,used_today:int,remaining_today:int,near_limit:bool,limit_reached:bool}
     */
    private function quotaState(User $user): array
    {
        $maxDailyInputs = $this->settingInt('max_daily_inputs', 50);
        $warningThreshold = max(1, $this->settingInt('daily_input_limit_warning_threshold', 1));

        $todayInputsCount = Input::query()
            ->where('user_id', $user->getKey())
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $remaining = max(0, $maxDailyInputs - $todayInputsCount);

        return [
            'daily_limit' => $maxDailyInputs,
            'used_today' => $todayInputsCount,
            'remaining_today' => $remaining,
            'near_limit' => $remaining <= $warningThreshold,
            'limit_reached' => $todayInputsCount >= $maxDailyInputs,
        ];
    }

    private function settingInt(string $key, int $default): int
    {
        $value = Setting::query()->where('key', $key)->value('value');

        return is_numeric($value) ? (int) $value : $default;
    }
}
