<?php

namespace App\Infra\Storage\Tests\Unit;

use App\Infra\Storage\S3CompatibleStorageProvider;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class S3CompatibleStorageProviderTest extends TestCase
{
    public function test_it_stores_temp_file_locally_and_points_media_to_s3_compatible_disk(): void
    {
        Storage::fake('local');

        $provider = new S3CompatibleStorageProvider(
            tempDisk: 'local',
            mediaDisk: 'spaces',
            mediaPrefix: '/inkai/'
        );

        $path = $provider->ingestTemporaryInput(
            inputId: 7,
            file: UploadedFile::fake()->create('start.png', 120, 'image/png')
        );

        Storage::disk('local')->assertExists($path);
        $this->assertSame('spaces', $provider->mediaDisk());
        $this->assertSame('inkai', $provider->mediaPrefix());
    }
}
