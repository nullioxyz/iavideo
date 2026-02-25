<?php

namespace App\Domain\Auth\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Domain\Invites\Models\Invite;
use App\Domain\Auth\Support\RoleNames;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable implements FilamentUser, HasName
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'must_reset_password',
        'email_verified_at',
        'username',
        'phone_number',
        'phone_number_verified_at',
        'password',
        'active',
        'credit_balance',
        'invited_by_user_id',
        'last_login_at',
        'suspended_at',
        'last_activity_at',
        'user_agent',
        'remember_token',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'last_login_at' => 'datetime',
            'suspended_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'must_reset_password' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function canAccessFilament(): bool
    {
        $user = User::query()->find($this->getKey());

        if (! $user) {
            return false;
        }

        return (bool) $user->active
            && $user->suspended_at === null
            && $user->hasAnyRole(RoleNames::adminPanelRoles());
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->canAccessFilament();
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'invited_by_user_id');
    }

    public function invitedUsers(): HasMany
    {
        return $this->hasMany(self::class, 'invited_by_user_id');
    }

    public function invitesSent(): HasMany
    {
        return $this->hasMany(Invite::class, 'invited_by_user_id');
    }

    /**
     * @return \App\Domain\Auth\Database\Factories\UserFactory
     */
    protected static function newFactory()
    {
        return \App\Domain\Auth\Database\Factories\UserFactory::new();
    }

    public function isActive(): bool
    {
        return $this->active;
    }
}
