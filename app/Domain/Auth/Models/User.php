<?php

namespace App\Domain\Auth\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Domain\Credits\Models\CreditLegder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function creditLedger(): HasMany
    {
        return $this->hasMany(CreditLegder::class, 'user_id');
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
