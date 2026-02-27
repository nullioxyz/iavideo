<?php

namespace App\Domain\Contacts\UseCases;

use App\Domain\Contacts\Models\Contact;

class CreateContactUseCase
{
    public function execute(array $data, bool $isUser): Contact
    {
        return Contact::query()->create([
            'name' => (string) $data['name'],
            'email' => (string) $data['email'],
            'phone' => isset($data['phone']) ? (string) $data['phone'] : null,
            'message' => (string) $data['message'],
            'is_user' => $isUser,
        ]);
    }
}

