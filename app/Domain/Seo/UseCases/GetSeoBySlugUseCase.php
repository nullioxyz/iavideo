<?php

namespace App\Domain\Seo\UseCases;

use App\Domain\Seo\Models\Seo;

class GetSeoBySlugUseCase
{
    public function execute(string $slug, ?int $preferredLanguageId, ?int $defaultLanguageId): ?Seo
    {
        $languageIds = array_values(array_filter([$preferredLanguageId, $defaultLanguageId]));

        return Seo::query()
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

