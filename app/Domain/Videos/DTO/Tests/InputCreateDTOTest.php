<?php

namespace App\Domain\Videos\DTO\Tests;

use App\Domain\Videos\DTO\InputCreateDTO;
use PHPUnit\Framework\TestCase;

class InputCreateDTOTest extends TestCase
{
    public function test_to_array_uses_explicit_title_when_provided(): void
    {
        $dto = new InputCreateDTO(
            presetId: 10,
            title: 'Titulo customizado',
            originalFilename: 'arquivo.png',
            mimeType: 'image/png',
            sizeBytes: 123,
            startImagePath: 'tmp/inputs/abc.png',
        );

        $payload = $dto->toArray(5);

        $this->assertSame('Titulo customizado', $payload['title']);
    }

    public function test_to_array_fallbacks_title_to_original_filename_when_title_is_null(): void
    {
        $dto = new InputCreateDTO(
            presetId: 10,
            title: null,
            originalFilename: 'arquivo.png',
            mimeType: 'image/png',
            sizeBytes: 123,
            startImagePath: 'tmp/inputs/abc.png',
        );

        $payload = $dto->toArray(5);

        $this->assertSame('arquivo.png', $payload['title']);
    }
}
