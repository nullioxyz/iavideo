<?php

namespace App\Infra\Storage\Tests\Unit;

use App\Infra\Storage\LocalStorageProvider;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LocalStorageProviderTest extends TestCase
{
    public function test_it_stores_temporary_file_on_local_disk_and_exposes_target_media_settings(): void
    {
        Storage::fake('local');

        $provider = new LocalStorageProvider(
            tempDisk: 'local',
            mediaDisk: 'public',
            mediaPrefix: 'local-prefix/'
        );

        $path = $provider->ingestTemporaryInput(
            inputId: 42,
            file: UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg')
        );

        Storage::disk('local')->assertExists($path);
        $this->assertSame('local', $provider->tempDisk());
        $this->assertSame('public', $provider->mediaDisk());
        $this->assertSame('local-prefix', $provider->mediaPrefix());
    }
}
