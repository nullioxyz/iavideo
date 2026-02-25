<?php

namespace App\Domain\Broadcasting\Events;

use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Resources\InputJobResource;

class UserJobUpdatedBroadcast extends BroadcastAbstractEvent
{
    /**
     * @param  array<string, mixed>  $job
     */
    public function __construct(
        private readonly int $userId,
        private readonly array $job,
    ) {}

    public static function fromInput(Input $input): self
    {
        $loadedInput = $input->loadMissing([
            'preset.model.platform',
            'prediction.outputs',
        ]);

        return new self(
            userId: (int) $loadedInput->user_id,
            job: (new InputJobResource($loadedInput))->resolve(),
        );
    }

    public function channelKey(): string
    {
        return 'user';
    }

    /**
     * @return array<string, int|string>
     */
    public function params(): array
    {
        return [
            'userId' => $this->userId,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return [
            'job' => $this->job,
        ];
    }
}
