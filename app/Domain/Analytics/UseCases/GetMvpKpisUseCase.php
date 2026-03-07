<?php

namespace App\Domain\Analytics\UseCases;

use App\Domain\Auth\Models\User;
use App\Domain\Contacts\Models\Contact;
use App\Domain\Credits\Models\CreditLedger;
use App\Domain\Videos\Models\Input;
use App\Domain\Videos\Models\Prediction;

class GetMvpKpisUseCase
{
    /**
     * @return array<string, int|float>
     */
    public function execute(): array
    {
        $totalUsers = User::query()->count();
        $usersWithInput = Input::query()->distinct('user_id')->count('user_id');

        $activationRate = $totalUsers > 0
            ? round(($usersWithInput / $totalUsers) * 100, 2)
            : 0.0;

        $avgReadySeconds = (float) Prediction::query()
            ->whereNotNull('finished_at')
            ->whereIn('status', [Prediction::SUCCEEDED, Prediction::FAILED, Prediction::CANCELLED])
            ->get(['created_at', 'finished_at'])
            ->avg(fn (Prediction $prediction): int => $prediction->created_at !== null && $prediction->finished_at !== null
                ? $prediction->created_at->diffInSeconds($prediction->finished_at)
                : 0);

        $totalPredictions = Prediction::query()->count();
        $failedPredictions = Prediction::query()->where('status', Prediction::FAILED)->count();
        $retrySuccessCount = Prediction::query()
            ->whereNotNull('retry_of_prediction_id')
            ->where('status', Prediction::SUCCEEDED)
            ->count();

        $failureRate = $totalPredictions > 0
            ? round(($failedPredictions / $totalPredictions) * 100, 2)
            : 0.0;

        $retrySuccessRate = $failedPredictions > 0
            ? round(($retrySuccessCount / $failedPredictions) * 100, 2)
            : 0.0;

        $completedInputIds = Input::query()
            ->where('status', Input::DONE)
            ->pluck('id');

        $debitedCredits = (int) abs((int) CreditLedger::query()
            ->whereIn('reference_id', $completedInputIds)
            ->where(function ($query): void {
                $query
                    ->where('operation_type', 'generation_debit')
                    ->orWhere('reference_type', 'input_creation');
            })
            ->sum('delta'));

        $completedVideos = $completedInputIds->count();

        $creditsPerCompletedVideo = $completedVideos > 0
            ? round($debitedCredits / $completedVideos, 2)
            : 0.0;

        $refundCredits = (int) CreditLedger::query()
            ->where(function ($query): void {
                $query
                    ->where('operation_type', 'generation_refund')
                    ->orWhereIn('reference_type', [
                        'input_prediction_creation_failed',
                        'input_prediction_creation_canceled',
                        'input_video_generation_failed',
                        'input_video_generation_canceled',
                    ]);
            })
            ->sum('delta');

        $refundRate = $debitedCredits > 0
            ? round(($refundCredits / $debitedCredits) * 100, 2)
            : 0.0;

        $contactsVolume = Contact::query()
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return [
            'activation_rate_percent' => $activationRate,
            'avg_time_to_ready_seconds' => round(max(0.0, $avgReadySeconds), 2),
            'prediction_failure_rate_percent' => $failureRate,
            'retry_success_rate_percent' => $retrySuccessRate,
            'credits_per_completed_video' => $creditsPerCompletedVideo,
            'refund_rate_percent' => $refundRate,
            'contacts_volume_last_30d' => $contactsVolume,
        ];
    }
}
