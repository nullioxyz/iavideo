<?php

namespace App\Domain\Videos\Models;

use App\Domain\AIModels\Models\Preset;
use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Input extends EloquentModel implements HasMedia
{
    use HasFactory, InteractsWithMedia, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'preset_id',
        'start_image_path',
        'original_filename',
        'mime_type',
        'size_bytes',
        'credit_debited',
        'credit_ledger_id',
        'status',
        'created_at',
        'updated_at',
    ];

    const CREATED = 'created';

    const PROCESSING = 'processing';

    const DONE = 'done';

    const FAILED = 'failed';

    const CANCELLED = 'cancelled';

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

    public function preset(): BelongsTo
    {
        return $this->belongsTo(Preset::class, 'preset_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function prediction(): HasOne
    {
        return $this->hasOne(Prediction::class, 'input_id');
    }

    /**
     * @return \App\Domain\Videos\Database\Factories\InputFactory
     */
    protected static function newFactory()
    {
        return \App\Domain\Videos\Database\Factories\InputFactory::new();
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('start_image')
            ->singleFile();
    }
}
