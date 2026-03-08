<?php

namespace App\Filament\Support\Tests\Unit;

use App\Filament\Support\FilamentUpload;
use Tests\TestCase;

class FilamentUploadTest extends TestCase
{
    public function test_it_uses_resolved_disk_and_prefixed_directory(): void
    {
        config()->set('uploads.provider', 's3');
        config()->set('uploads.temp_disk', 'local');
        config()->set('uploads.s3.media_disk', 'spaces');
        config()->set('uploads.s3.media_prefix', '');
        $this->app['env'] = 'production';

        $this->assertSame('local', FilamentUpload::disk());
        $this->assertSame('spaces', FilamentUpload::mediaDisk());
        $this->assertSame('inkai/seo/uploads', FilamentUpload::directory('seo/uploads'));
    }
}
