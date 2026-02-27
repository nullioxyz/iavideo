<?php

namespace App\Domain\Analytics\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Analytics extends EloquentModel
{
    protected $table = 'analytics';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'provider',
        'tracking_id',
        'script_head',
        'script_body',
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

