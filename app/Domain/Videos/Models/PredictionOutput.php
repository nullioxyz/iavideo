<?php

namespace App\Domain\Videos\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PredictionOutput extends EloquentModel implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'prediction_id',
        'kind',
        'path',
        'mime_type',
        'size_bytes',
        'created_at',
        'updated_at'
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

    public function prediction(): BelongsTo
    {
        return $this->belongsTo(Prediction::class, 'prediction_id');
    }

    /**
     * @return \App\Domain\Videos\Database\Factories\PredictionOutputFactory
     */
    protected static function newFactory()
    {
        return \App\Domain\Videos\Database\Factories\PredictionOutputFactory::new();
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('file')
            ->singleFile();
    }

    public function getMediaFile(): ?\Spatie\MediaLibrary\MediaCollections\Models\Media
    {
        return $this->getFirstMedia('file');
    }

}
