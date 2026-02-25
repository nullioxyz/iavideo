<?php

namespace App\Domain\Auth\Models;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginAudit extends EloquentModel
{
    protected $table = 'login_audits';

    const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'email',
        'success',
        'failure_reason',
        'ip_address',
        'forwarded_for',
        'country_code',
        'region',
        'city',
        'user_agent',
        'browser',
        'platform',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'success' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

