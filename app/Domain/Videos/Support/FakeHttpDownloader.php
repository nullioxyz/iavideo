<?php

namespace App\Domain\Videos\Support;

use Illuminate\Support\Facades\Http;
use Spatie\MediaLibrary\Downloaders\Downloader;
use Spatie\MediaLibrary\MediaCollections\Exceptions\UnreachableUrl;

class FakeHttpDownloader implements Downloader
{
    public function getTempFile($url): string
    {
        $response = Http::get($url);

        if (! $response->successful()) {
            throw UnreachableUrl::create($url);
        }

        $ext = pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION) ?: 'bin';

        $tmp = tempnam(sys_get_temp_dir(), 'media-library-');
        $tmpWithExt = $tmp.'.'.$ext;

        rename($tmp, $tmpWithExt);

        file_put_contents($tmpWithExt, $response->body());

        return $tmpWithExt;
    }
}
