<?php

namespace App\Domain\Auth\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImpersonationLink extends EloquentModel
{
    protected $table = 'impersonation_links';

    const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'actor_user_id',
        'target_user_id',
        'token_hash',
        'expires_at',
        'used_at',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}

