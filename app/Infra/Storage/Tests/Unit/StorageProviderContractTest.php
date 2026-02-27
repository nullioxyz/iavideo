<?php

namespace App\Infra\Storage\Tests\Unit;

use App\Infra\Storage\Contracts\StorageProviderInterface;
use App\Infra\Storage\LocalStorageProvider;
use App\Infra\Storage\S3CompatibleStorageProvider;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageProviderContractTest extends TestCase
{
    public function test_implementations_follow_storage_provider_contract(): void
    {
        Storage::fake('local');

        foreach ($this->providers() as $provider) {
            $path = $provider->ingestTemporaryInput(
                inputId: 10,
                file: UploadedFile::fake()->create('image.png', 50, 'image/png')
            );

            $this->assertStringStartsWith('tmp/inputs/10/', $path);
            $this->assertStringEndsWith('.png', $path);
            $this->assertNotSame('', $provider->tempDisk());
            $this->assertNotSame('', $provider->mediaDisk());
            Storage::disk($provider->tempDisk())->assertExists($path);
        }
    }

    /**
     * @return array<StorageProviderInterface>
     */
    private function providers(): array
    {
        return [
            new LocalStorageProvider(
                tempDisk: 'local',
                mediaDisk: 'public',
                mediaPrefix: ''
            ),
            new S3CompatibleStorageProvider(
                tempDisk: 'local',
                mediaDisk: 'spaces',
                mediaPrefix: 'inkai'
            ),
        ];
    }
}
