<?php

namespace App\Domain\AIModels\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Notifications\Notifiable;

class Model extends EloquentModel
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'platform_id',
        'name',
        'slug',
        'version',
        'active',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return \App\Domain\AIModels\Database\Factories\ModelFactory
     */
    protected static function newFactory()
    {
        return \App\Domain\AIModels\Database\Factories\ModelFactory::new();
    }
}
