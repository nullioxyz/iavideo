<?php

namespace App\Domain\Operations\Tests\Integration;

use Tests\TestCase;

class RequestContextMiddlewareTest extends TestCase
{
    public function test_it_attaches_generated_request_id_header(): void
    {
        $response = $this->get('/up');

        $response->assertOk();
        $requestId = $response->headers->get('X-Request-Id');

        $this->assertIsString($requestId);
        $this->assertNotSame('', trim((string) $requestId));
    }

    public function test_it_preserves_incoming_request_id_header(): void
    {
        $response = $this->withHeader('X-Request-Id', 'my-custom-request-id')
            ->get('/up');

        $response->assertOk();
        $response->assertHeader('X-Request-Id', 'my-custom-request-id');
    }
}
