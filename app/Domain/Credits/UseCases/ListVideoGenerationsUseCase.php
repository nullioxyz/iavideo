<?php

namespace App\Domain\Credits\UseCases;

use App\Domain\Credits\Models\CreditLedger;
use App\Domain\Videos\Models\Input;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListVideoGenerationsUseCase
{
    public function execute(int $userId, int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));
        $page = max(1, $page);

        $paginator = Input::query()
            ->where('user_id', $userId)
            ->with([
                'preset:id,name',
                'prediction.outputs.media',
            ])
            ->orderByDesc('id')
            ->paginate(perPage: $perPage, page: $page);

        $inputIds = collect($paginator->items())
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $ledgersByInput = [];

        if ($inputIds !== []) {
            $entries = CreditLedger::query()
                ->where('user_id', $userId)
                ->whereIn('reference_id', $inputIds)
                ->whereIn('reference_type', [
                    'input_creation',
                    'input_video_generation_failed',
                    'input_prediction_creation_failed',
                    'input_video_generation_canceled',
                ])
                ->orderBy('id')
                ->get();

            $ledgersByInput = $entries->groupBy('reference_id')->all();
        }

        foreach ($paginator->items() as $input) {
            $entries = $ledgersByInput[$input->id] ?? collect();

            if (is_array($entries)) {
                $entries = collect($entries);
            }

            $creditsDebited = (int) abs($entries
                ->where('reference_type', 'input_creation')
                ->sum('delta'));

            $creditsRefunded = (int) $entries
                ->whereIn('reference_type', [
                    'input_video_generation_failed',
                    'input_prediction_creation_failed',
                    'input_video_generation_canceled',
                ])
                ->sum(fn ($entry) => max(0, (int) $entry->delta));

            $creditsUsed = max(0, $creditsDebited - $creditsRefunded);

            $refundEntries = $entries
                ->whereIn('reference_type', [
                    'input_video_generation_failed',
                    'input_prediction_creation_failed',
                    'input_video_generation_canceled',
                ])
                ->filter(fn ($entry) => (int) $entry->delta > 0)
                ->values();

            $ledgerEntries = $entries
                ->values()
                ->map(function ($entry): array {
                    $delta = (int) $entry->delta;

                    return [
                        'ledger_id' => (int) $entry->id,
                        'type' => $delta < 0 ? 'debit' : 'refund',
                        'operation' => $delta < 0 ? 'debit' : 'refund',
                        'delta' => $delta,
                        'balance_after' => (int) $entry->balance_after,
                        'reason' => (string) $entry->reason,
                        'reference_type' => (string) $entry->reference_type,
                        'reference_id' => (int) $entry->reference_id,
                        'created_at' => $entry->created_at?->toISOString(),
                    ];
                })
                ->values()
                ->all();

            $prediction = $input->prediction;
            $inputStatus = (string) ($input->status ?? '');
            $predictionStatus = (string) ($prediction?->status ?? '');

            $isCanceled = in_array($inputStatus, ['cancelled'], true)
                || in_array($predictionStatus, ['cancelled'], true);

            $isFailed = in_array($inputStatus, ['failed'], true)
                || in_array($predictionStatus, ['failed'], true);

            $isRefunded = $creditsRefunded > 0;

            $cancelEntry = $refundEntries
                ->first(fn ($entry) => (string) $entry->reference_type === 'input_video_generation_canceled');

            $failureEntry = $refundEntries
                ->first(fn ($entry) => in_array((string) $entry->reference_type, [
                    'input_video_generation_failed',
                    'input_prediction_creation_failed',
                ], true));

            $input->setAttribute('credits_debited', $creditsDebited);
            $input->setAttribute('credits_refunded', $creditsRefunded);
            $input->setAttribute('credits_used', $creditsUsed);
            $input->setAttribute('is_failed', $isFailed);
            $input->setAttribute('is_canceled', $isCanceled);
            $input->setAttribute('is_refunded', $isRefunded);
            $input->setAttribute('ledger_entries', $ledgerEntries);
            $input->setAttribute('credit_events', $ledgerEntries);
            $input->setAttribute('ledger_entries_count', count($ledgerEntries));
            $input->setAttribute('failure_reason', $failureEntry?->reason);
            $input->setAttribute('cancel_reason', $cancelEntry?->reason);
            $input->setAttribute('failure_code', $prediction?->error_code);
            $input->setAttribute('failure_message', $prediction?->error_message);
        }

        return $paginator;
    }
}
