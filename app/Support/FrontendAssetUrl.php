<?php

namespace App\Support;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FrontendAssetUrl
{
    public static function image(?Media $media): ?string
    {
        if (! $media instanceof Media || ! is_numeric($media->getKey())) {
            return null;
        }

        return self::frontendBase().'/image/'.self::encodeMediaToken((int) $media->getKey()).'/image';
    }

    public static function video(?Media $media): ?string
    {
        if (! $media instanceof Media || ! is_numeric($media->getKey())) {
            return null;
        }

        $filename = trim((string) $media->file_name);
        if ($filename === '') {
            $filename = 'video.mp4';
        }

        return self::frontendBase().'/video/'.self::encodeMediaToken((int) $media->getKey()).'/'.rawurlencode($filename);
    }

    public static function resolveExternal(?string $url): ?string
    {
        $value = trim((string) $url);
        if ($value === '') {
            return null;
        }

        $frontendBase = self::frontendBase();
        $appBase = rtrim((string) config('app.url', ''), '/');

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            if (self::isSameHost($value, $appBase)) {
                return self::replaceBase($value, $frontendBase);
            }

            return $value;
        }

        return $frontendBase.'/'.ltrim($value, '/');
    }

    public static function decodeMediaToken(string $token): ?int
    {
        $decoded = self::base64UrlDecode($token);
        if ($decoded === null) {
            return null;
        }

        try {
            $plain = Crypt::decryptString($decoded);
        } catch (DecryptException) {
            return null;
        }

        if (! is_numeric($plain)) {
            return null;
        }

        return (int) $plain;
    }

    private static function encodeMediaToken(int $id): string
    {
        return self::base64UrlEncode(Crypt::encryptString((string) $id));
    }

    private static function frontendBase(): string
    {
        return rtrim((string) config('app.frontend_url', config('app.url')), '/');
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

    private static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $value): ?string
    {
        $normalized = strtr($value, '-_', '+/');
        $padding = strlen($normalized) % 4;
        if ($padding > 0) {
            $normalized .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($normalized, true);

        return $decoded === false ? null : $decoded;
    }
}
