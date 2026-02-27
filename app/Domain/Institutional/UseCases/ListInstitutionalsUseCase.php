<?php

namespace App\Domain\Institutional\UseCases;

use App\Domain\Institutional\Models\Institutional;
use Illuminate\Database\Eloquent\Collection;

class ListInstitutionalsUseCase
{
    /**
     * @return Collection<int, Institutional>
     */
    public function execute(?int $preferredLanguageId, ?int $defaultLanguageId): Collection
    {
        $languageIds = array_values(array_filter([$preferredLanguageId, $defaultLanguageId]));

        return Institutional::query()
            ->where('active', true)
            ->with([
                'translations' => function ($query) use ($languageIds): void {
                    if ($languageIds !== []) {
                        $query->whereIn('language_id', $languageIds);
                    }
                },
                'media',
            ])
            ->orderBy('id')
            ->get();
    }
}

