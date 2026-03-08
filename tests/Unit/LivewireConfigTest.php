<?php

namespace Tests\Unit;

use Tests\TestCase;

class LivewireConfigTest extends TestCase
{
    public function test_livewire_temporary_upload_disk_defaults_to_local_temp_disk(): void
    {
        config()->set('uploads.temp_disk', 'local');
        config()->set('livewire.temporary_file_upload.disk', env('LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK', env('UPLOADS_TEMP_DISK', 'local')));

        $this->assertSame('local', config('livewire.temporary_file_upload.disk'));
    }

    public function test_livewire_temporary_upload_disk_can_be_overridden_explicitly(): void
    {
        config()->set('livewire.temporary_file_upload.disk', 'tmp-uploads');

        $this->assertSame('tmp-uploads', config('livewire.temporary_file_upload.disk'));
    }
}
