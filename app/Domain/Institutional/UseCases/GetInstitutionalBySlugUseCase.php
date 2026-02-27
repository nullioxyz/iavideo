<?php

namespace App\Domain\Institutional\UseCases;

use App\Domain\Institutional\Models\Institutional;

class GetInstitutionalBySlugUseCase
{
    public function execute(string $slug, ?int $preferredLanguageId, ?int $defaultLanguageId): ?Institutional
    {
        $languageIds = array_values(array_filter([$preferredLanguageId, $defaultLanguageId]));

        return Institutional::query()
            ->where('active', true)
            ->where(function ($query) use ($slug, $languageIds): void {
                $query->where('slug', $slug)
                    ->orWhereHas('translations', function ($translationQuery) use ($slug, $languageIds): void {
                        if ($languageIds !== []) {
                            $translationQuery->whereIn('language_id', $languageIds);
                        }

                        $translationQuery->where('slug', $slug);
                    });
            })
            ->with([
                'translations' => function ($query) use ($languageIds): void {
                    if ($languageIds !== []) {
                        $query->whereIn('language_id', $languageIds);
                    }
                },
                'media',
            ])
            ->first();
    }
}

