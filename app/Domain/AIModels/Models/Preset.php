<?php

namespace App\Domain\AIModels\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class Preset extends EloquentModel
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'prompt',
        'negative_prompt',
        'aspect_ratio',
        'duration_seconds',
        'default_model_id',
        'cost_estimate_usd',
        'preview_video_url',
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

    public function model(): BelongsTo
    {
        return $this->belongsTo(Model::class, 'default_model_id');
    }

    /**
     * @return \App\Domain\AIModels\Database\Factories\PresetFactory
     */
    protected static function newFactory()
    {
        return \App\Domain\AIModels\Database\Factories\PresetFactory::new();
    }
}
