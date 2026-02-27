<?php

namespace App\Domain\Contacts\Tests\Integration;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use App\Domain\Contacts\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactsApiTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_guest_can_create_contact_and_is_marked_as_non_user(): void
    {
        $payload = [
            'name' => 'Guest Person',
            'email' => 'guest@example.com',
            'phone' => '+55 11 99999-9999',
            'message' => 'I need help with presets.',
        ];

        $response = $this->postJson('/api/contacts', $payload);

        $response->assertCreated();
        $id = (int) $response->json('data.id');

        $this->assertDatabaseHas('contacts', [
            'id' => $id,
            'name' => $payload['name'],
            'email' => $payload['email'],
            'is_user' => false,
        ]);
    }

    public function test_authenticated_user_can_create_contact_and_is_marked_as_user(): void
    {
        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
        ]);

        $token = $this->loginAndGetToken($user);

        $response = $this->withJwt($token)->postJson('/api/contacts', [
            'name' => 'Authenticated User',
            'email' => 'auth@example.com',
            'message' => 'Need billing support.',
        ]);

        $response->assertCreated();

        $contact = Contact::query()->findOrFail((int) $response->json('data.id'));
        $this->assertTrue((bool) $contact->is_user);
    }

    public function test_create_contact_validates_required_fields(): void
    {
        $response = $this->postJson('/api/contacts', [
            'name' => '',
            'email' => 'invalid-email',
            'message' => '',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name', 'email', 'message']);
    }
}
