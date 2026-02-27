<?php

namespace App\Support\Tests\Unit;

use App\Support\FrontendAssetUrl;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class FrontendAssetUrlTest extends TestCase
{
    public function test_it_builds_encrypted_image_route_url(): void
    {
        config()->set('app.frontend_url', 'https://inkai.video');

        $media = new Media;
        $media->id = 123;
        $media->file_name = 'image.png';

        $url = FrontendAssetUrl::image($media);

        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://inkai.video/image/', (string) $url);
        $this->assertStringEndsWith('/image', (string) $url);
    }

    public function test_it_builds_encrypted_video_route_url_with_filename(): void
    {
        config()->set('app.frontend_url', 'https://inkai.video');

        $media = new Media;
        $media->id = 456;
        $media->file_name = 'arquivo.mp4';

        $url = FrontendAssetUrl::video($media);

        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://inkai.video/video/', (string) $url);
        $this->assertStringEndsWith('/arquivo.mp4', (string) $url);
    }

    public function test_it_decodes_media_token_from_generated_url(): void
    {
        config()->set('app.frontend_url', 'https://inkai.video');

        $media = new Media;
        $media->id = 789;
        $media->file_name = 'video.mp4';

        $url = (string) FrontendAssetUrl::video($media);
        $token = explode('/', parse_url($url, PHP_URL_PATH) ?? '')[2] ?? '';

        $this->assertSame(789, FrontendAssetUrl::decodeMediaToken($token));
    }

    public function test_it_replaces_app_host_with_frontend_host_for_external_resolve(): void
    {
        config()->set('app.url', 'http://laravel.test');
        config()->set('app.frontend_url', 'https://inkai.video');

        $this->assertSame(
            'https://inkai.video/storage/foo.png?x=1',
            FrontendAssetUrl::resolveExternal('http://laravel.test/storage/foo.png?x=1')
        );
    }

    public function test_it_keeps_external_urls_untouched_for_external_resolve(): void
    {
        config()->set('app.url', 'http://laravel.test');
        config()->set('app.frontend_url', 'https://inkai.video');

        $this->assertSame(
            'https://cdn.example.com/image.png',
            FrontendAssetUrl::resolveExternal('https://cdn.example.com/image.png')
        );
    }
}
