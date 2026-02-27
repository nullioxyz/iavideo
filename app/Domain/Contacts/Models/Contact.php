<?php

namespace App\Domain\Contacts\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Contact extends EloquentModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'is_user',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_user' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}

