<?php

namespace App\Infra\Storage;

use App\Infra\Storage\Contracts\StorageProviderInterface;

class StorageProviderFactory
{
    public function make(): StorageProviderInterface
    {
        $provider = UploadStorageResolver::provider();

        if ($provider === 's3') {
            return new S3CompatibleStorageProvider(
                tempDisk: UploadStorageResolver::tempDisk(),
                mediaDisk: UploadStorageResolver::mediaDisk(),
                mediaPrefix: UploadStorageResolver::mediaPrefix(),
            );
        }

        return new LocalStorageProvider(
            tempDisk: UploadStorageResolver::tempDisk(),
            mediaDisk: UploadStorageResolver::mediaDisk(),
            mediaPrefix: UploadStorageResolver::mediaPrefix(),
        );
    }
}
