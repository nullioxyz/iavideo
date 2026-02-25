<?php

namespace App\Domain\Auth\Support;

use App\Domain\Auth\DTO\LoginContextDTO;
use Illuminate\Http\Request;

final class LoginContextResolver
{
    public function fromRequest(Request $request): LoginContextDTO
    {
        $userAgent = $request->userAgent();

        return new LoginContextDTO(
            ipAddress: $request->ip(),
            forwardedFor: $this->headerValue($request, 'X-Forwarded-For'),
            countryCode: $this->headerValue($request, 'CF-IPCountry')
                ?? $this->headerValue($request, 'X-Country-Code'),
            region: $this->headerValue($request, 'X-Region'),
            city: $this->headerValue($request, 'X-City'),
            userAgent: $userAgent,
            browser: $this->resolveBrowser($userAgent),
            platform: $this->resolvePlatform($userAgent),
        );
    }

    private function headerValue(Request $request, string $key): ?string
    {
        $value = trim((string) $request->header($key, ''));

        return $value !== '' ? $value : null;
    }

    private function resolveBrowser(?string $userAgent): ?string
    {
        $ua = mb_strtolower((string) $userAgent);
        if ($ua === '') {
            return null;
        }

        if (str_contains($ua, 'edg/')) {
            return 'Edge';
        }

        if (str_contains($ua, 'opr/') || str_contains($ua, 'opera')) {
            return 'Opera';
        }

        if (str_contains($ua, 'chrome/') && ! str_contains($ua, 'chromium')) {
            return 'Chrome';
        }

        if (str_contains($ua, 'firefox/')) {
            return 'Firefox';
        }

        if (str_contains($ua, 'safari/') && ! str_contains($ua, 'chrome/')) {
            return 'Safari';
        }

        return 'Unknown';
    }

    private function resolvePlatform(?string $userAgent): ?string
    {
        $ua = mb_strtolower((string) $userAgent);
        if ($ua === '') {
            return null;
        }

        if (str_contains($ua, 'windows')) {
            return 'Windows';
        }

        if (str_contains($ua, 'mac os') || str_contains($ua, 'macintosh')) {
            return 'macOS';
        }

        if (str_contains($ua, 'iphone') || str_contains($ua, 'ipad') || str_contains($ua, 'ios')) {
            return 'iOS';
        }

        if (str_contains($ua, 'android')) {
            return 'Android';
        }

        if (str_contains($ua, 'linux')) {
            return 'Linux';
        }

        return 'Unknown';
    }
}

