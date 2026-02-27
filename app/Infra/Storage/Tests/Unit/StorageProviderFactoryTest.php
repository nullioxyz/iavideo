<?php

namespace App\Infra\Storage\Tests\Unit;

use App\Infra\Storage\LocalStorageProvider;
use App\Infra\Storage\S3CompatibleStorageProvider;
use App\Infra\Storage\StorageProviderFactory;
use Tests\TestCase;

class StorageProviderFactoryTest extends TestCase
{
    public function test_it_selects_local_provider_when_configured(): void
    {
        config()->set('uploads.provider', 'local');
        config()->set('uploads.temp_disk', 'local');
        config()->set('uploads.local.media_disk', 'public');
        config()->set('uploads.local.media_prefix', '');

        $provider = (new StorageProviderFactory)->make();

        $this->assertInstanceOf(LocalStorageProvider::class, $provider);
        $this->assertSame('public', $provider->mediaDisk());
    }

    public function test_it_selects_s3_provider_and_enforces_inkai_prefix_in_production(): void
    {
        config()->set('uploads.provider', 's3');
        config()->set('uploads.temp_disk', 'local');
        config()->set('uploads.s3.media_disk', 'spaces');
        config()->set('uploads.s3.media_prefix', '');
        $this->app['env'] = 'production';

        $provider = (new StorageProviderFactory)->make();

        $this->assertInstanceOf(S3CompatibleStorageProvider::class, $provider);
        $this->assertSame('spaces', $provider->mediaDisk());
        $this->assertSame('inkai', $provider->mediaPrefix());
    }
}
