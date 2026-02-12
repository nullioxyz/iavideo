<?php

namespace App\Domain\Invites\Models;

use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Invite extends EloquentModel
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'token',
        'credits_granted',
        'invited_by_user_id',
        'used_at',
        'expires_at',
        'created_at',
        'updated_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $invite): void {
            if (! $invite->token) {
                $invite->token = Str::uuid()->toString();
            }
        });
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function getShareUrlAttribute(): string
    {
        $pattern = (string) config(
            'invites.share_url_pattern',
            rtrim((string) config('app.url'), '/').'/invite/{token}'
        );

        return str_replace('{token}', (string) $this->token, $pattern);
    }

    protected static function newFactory()
    {
        return \App\Domain\Invites\Database\Factories\InviteFactory::new();
    }
}
