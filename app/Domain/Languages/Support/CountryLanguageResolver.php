<?php

namespace App\Domain\Languages\Support;

use App\Domain\Languages\Models\Language;

class CountryLanguageResolver
{
    private const COUNTRY_TO_LANGUAGE = [
        'BR' => 'pt-BR',
        'IT' => 'it',
    ];

    public function resolveFromCountryCode(?string $countryCode): ?int
    {
        $normalizedCountry = strtoupper(trim((string) $countryCode));
        if ($normalizedCountry === '') {
            return null;
        }

        $slug = self::COUNTRY_TO_LANGUAGE[$normalizedCountry] ?? 'en';

        $languageId = Language::query()
            ->where('slug', $slug)
            ->where('active', true)
            ->value('id');

        return is_numeric($languageId) ? (int) $languageId : null;
    }

    public function resolveFromAcceptLanguage(?string $acceptLanguage): ?int
    {
        $value = trim((string) $acceptLanguage);
        if ($value === '') {
            return null;
        }

        $prioritizedSlugs = [];
        $parts = explode(',', $value);

        foreach ($parts as $part) {
            $token = strtolower(trim(explode(';', $part)[0]));
            if ($token === '') {
                continue;
            }

            $normalized = str_replace('_', '-', $token);
            $prioritizedSlugs[] = $this->normalizeLanguageTokenToSlug($normalized);
        }

        $prioritizedSlugs = array_values(array_unique(array_filter($prioritizedSlugs)));

        foreach ($prioritizedSlugs as $slug) {
            $languageId = Language::query()
                ->where('slug', $slug)
                ->where('active', true)
                ->value('id');

            if (is_numeric($languageId)) {
                return (int) $languageId;
            }
        }

        return null;
    }

    private function normalizeLanguageTokenToSlug(string $token): ?string
    {
        return match (true) {
            $token === 'pt-br' => 'pt-BR',
            $token === 'pt' => 'pt-BR',
            $token === 'it' => 'it',
            $token === 'en' => 'en',
            default => match (strtok($token, '-')) {
                'pt' => 'pt-BR',
                'it' => 'it',
                'en' => 'en',
                default => null,
            },
        };
    }
}
