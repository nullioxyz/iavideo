<?php

namespace App\Support;

class FrontendAssetUrl
{
    public static function resolve(?string $url): ?string
    {
        $value = trim((string) $url);
        if ($value === '') {
            return null;
        }

        $frontendBase = rtrim((string) config('app.frontend_url', config('app.url')), '/');
        $appBase = rtrim((string) config('app.url', ''), '/');

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            if (self::isSameHost($value, $appBase)) {
                return self::replaceBase($value, $frontendBase);
            }

            return $value;
        }

        return $frontendBase.'/'.ltrim($value, '/');
    }

    private static function isSameHost(string $left, string $right): bool
    {
        $leftHost = strtolower((string) parse_url($left, PHP_URL_HOST));
        $rightHost = strtolower((string) parse_url($right, PHP_URL_HOST));

        return $leftHost !== '' && $leftHost === $rightHost;
    }

    private static function replaceBase(string $url, string $newBase): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        $query = (string) parse_url($url, PHP_URL_QUERY);
        $fragment = (string) parse_url($url, PHP_URL_FRAGMENT);

        $result = rtrim($newBase, '/').'/'.ltrim($path, '/');

        if ($query !== '') {
            $result .= '?'.$query;
        }

        if ($fragment !== '') {
            $result .= '#'.$fragment;
        }

        return $result;
    }
}
