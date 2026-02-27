<?php

namespace App\Infra\Storage\Tests\Unit;

use App\Infra\Storage\UploadStorageResolver;
use Tests\TestCase;

class UploadStorageResolverTest extends TestCase
{
    public function test_it_resolves_local_media_disk_when_provider_is_local(): void
    {
        config()->set('uploads.provider', 'local');
        config()->set('uploads.local.media_disk', 'public');
        config()->set('uploads.local.media_prefix', '');

        $this->assertSame('local', UploadStorageResolver::provider());
        $this->assertSame('public', UploadStorageResolver::mediaDisk());
        $this->assertSame('', UploadStorageResolver::mediaPrefix());
        $this->assertSame('seo/uploads', UploadStorageResolver::prefixedDirectory('seo/uploads'));
    }

    public function test_it_enforces_inkai_prefix_for_s3_in_production_when_prefix_is_empty(): void
    {
        config()->set('uploads.provider', 's3');
        config()->set('uploads.s3.media_disk', 'spaces');
        config()->set('uploads.s3.media_prefix', '');
        $this->app['env'] = 'production';

        $this->assertSame('spaces', UploadStorageResolver::mediaDisk());
        $this->assertSame('inkai', UploadStorageResolver::mediaPrefix());
        $this->assertSame(
            'inkai/presets/uploads/images',
            UploadStorageResolver::prefixedDirectory('presets/uploads/images')
        );
    }
}
