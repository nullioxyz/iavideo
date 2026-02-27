<?php

namespace App\Domain\Languages\Support;

use App\Domain\Auth\Models\User;
use App\Domain\Languages\Models\Language;
use Illuminate\Http\Request;

class CountryLanguageContextResolver
{
    public function __construct(
        private readonly CountryLanguageResolver $countryLanguageResolver,
    ) {}

    /**
     * @return array{preferred_language_id:?int,default_language_id:?int,preferred_language_slug:?string,default_language_slug:?string}
     */
    public function fromRequest(Request $request, ?User $user = null): array
    {
        $cached = $request->attributes->get('country_language_context');
        if (is_array($cached)) {
            return $cached;
        }

        $defaultLanguage = Language::query()
            ->where('active', true)
            ->where('is_default', true)
            ->first()
            ?? Language::query()->where('active', true)->orderBy('id')->first();

        $preferredLanguageId = $this->resolvePreferredLanguageId($request, $user);
        $preferredLanguageSlug = null;

        if ($preferredLanguageId !== null) {
            $preferredLanguageSlug = Language::query()->whereKey($preferredLanguageId)->value('slug');
        }

        $context = [
            'preferred_language_id' => $preferredLanguageId,
            'default_language_id' => $defaultLanguage ? (int) $defaultLanguage->getKey() : null,
            'preferred_language_slug' => is_string($preferredLanguageSlug) ? $preferredLanguageSlug : null,
            'default_language_slug' => $defaultLanguage ? (string) $defaultLanguage->slug : null,
        ];

        $request->attributes->set('country_language_context', $context);

        return $context;
    }

    private function resolvePreferredLanguageId(Request $request, ?User $user = null): ?int
    {
        if ($user instanceof User && is_numeric($user->language_id)) {
            $languageId = Language::query()
                ->whereKey((int) $user->language_id)
                ->where('active', true)
                ->value('id');

            if (is_numeric($languageId)) {
                return (int) $languageId;
            }
        }

        $countryCode = $this->resolveCountryCodeFromHeaders($request);
        if ($countryCode === '') {
            $countryCode = (string) ($user?->country_code ?? '');
        }

        if ($countryCode === '') {
            // Backward-compatible optional query override.
            $countryCode = (string) $request->query('country_code', '');
        }

        $fromCountry = $this->countryLanguageResolver->resolveFromCountryCode($countryCode);
        if ($fromCountry !== null) {
            return $fromCountry;
        }

        return $this->countryLanguageResolver->resolveFromAcceptLanguage(
            (string) $request->header('Accept-Language', '')
        );
    }

    private function resolveCountryCodeFromHeaders(Request $request): string
    {
        $headers = config('language_context.country_headers', []);
        if (! is_array($headers) || $headers === []) {
            return '';
        }

        foreach ($headers as $headerName) {
            if (! is_string($headerName) || trim($headerName) === '') {
                continue;
            }

            $value = trim((string) $request->header($headerName, ''));
            if ($value !== '') {
                return strtoupper($value);
            }
        }

        return '';
    }
}
