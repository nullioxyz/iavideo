<?php

namespace App\Domain\Videos\Models;

use App\Domain\AIModels\Models\Model;
use App\Domain\AIModels\Models\Preset;
use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prediction extends EloquentModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'input_id',
        'model_id',
        'external_id',
        'status',
        'source',
        'attempt',
        'retry_of_prediction_id',
        'queued_at',
        'started_at',
        'finished_at',
        'failed_at',
        'cost_estimate_usd',
        'cost_actual_usd',
        'error_code',
        'error_message',
        'request_payload',
        'response_payload',
        'created_at',
        'updated_at'
    ];

    public const QUEUED     = 'queued';
    public const STARTING   = 'starting';
    public const SUBMITTING = 'submitting';
    public const PROCESSING = 'processing';
    public const SUCCEEDED  = 'succeeded';
    public const FAILED     = 'failed';
    public const CANCELLED  = 'cancelled';
    public const REFUNDED   = 'refunded';

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
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'failed_at' => 'datetime',
            'request_payload' => 'array',
            'response_payload' => 'array',
        ];
    }

    public function input(): BelongsTo
    {
        return $this->belongsTo(Input::class, 'input_id');
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(Model::class, 'model_id');
    }

    public function outputs(): HasMany
    {
        return $this->hasMany(PredictionOutput::class);
    }

    /**
     * @return \App\Domain\Videos\Database\Factories\PredictionFactory
     */
    protected static function newFactory()
    {
        return \App\Domain\Videos\Database\Factories\PredictionFactory::new();
    }


}
