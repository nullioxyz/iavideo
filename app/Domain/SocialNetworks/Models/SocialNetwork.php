<?php

namespace App\Domain\SocialNetworks\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class SocialNetwork extends EloquentModel
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'url',
        'slug',
        'active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}

