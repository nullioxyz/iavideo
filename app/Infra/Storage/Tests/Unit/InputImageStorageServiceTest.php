<?php

namespace App\Infra\Storage\Tests\Unit;

use App\Infra\Storage\Contracts\StorageProviderInterface;
use App\Infra\Storage\InputImageStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Tests\TestCase;

class InputImageStorageServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_it_ingests_and_checks_temp_files_using_provider_disk(): void
    {
        Storage::fake('local');

        $provider = Mockery::mock(StorageProviderInterface::class);
        $provider->shouldReceive('tempDisk')->andReturn('local');

        $service = new InputImageStorageService($provider);

        $tempPath = $service->ingestTemporaryInput(
            inputId: 88,
            file: UploadedFile::fake()->create('frame.png', 100, 'image/png')
        );

        $this->assertTrue($service->tempFileExists($tempPath));
        Storage::disk('local')->assertExists($tempPath);
        $this->assertFalse($service->tempFileExists("tmp/inputs/88/missing.png"));
    }
}
