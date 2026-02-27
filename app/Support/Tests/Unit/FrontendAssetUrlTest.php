<?php

namespace App\Support\Tests\Unit;

use App\Support\FrontendAssetUrl;
use Tests\TestCase;

class FrontendAssetUrlTest extends TestCase
{
    public function test_it_builds_frontend_url_for_relative_paths(): void
    {
        config()->set('app.frontend_url', 'https://inkai.video');

        $this->assertSame(
            'https://inkai.video/storage/foo.png',
            FrontendAssetUrl::resolve('/storage/foo.png')
        );
    }

    public function test_it_replaces_app_host_with_frontend_host(): void
    {
        config()->set('app.url', 'http://laravel.test');
        config()->set('app.frontend_url', 'https://inkai.video');

        $this->assertSame(
            'https://inkai.video/storage/foo.png?x=1',
            FrontendAssetUrl::resolve('http://laravel.test/storage/foo.png?x=1')
        );
    }

    public function test_it_keeps_external_urls_untouched(): void
    {
        config()->set('app.url', 'http://laravel.test');
        config()->set('app.frontend_url', 'https://inkai.video');

        $this->assertSame(
            'https://cdn.example.com/image.png',
            FrontendAssetUrl::resolve('https://cdn.example.com/image.png')
        );
    }
}
