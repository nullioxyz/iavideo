<?php

namespace App\Domain\Credits\Models;

use App\Domain\AIModels\Models\Model as AIModel;
use App\Domain\AIModels\Models\Preset;
use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditLedger extends EloquentModel
{
    use HasFactory;

    protected $table = 'credit_ledger';

    const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'delta',
        'balance_before',
        'balance_after',
        'reason',
        'operation_type',
        'reference_type',
        'reference_id',
        'model_id',
        'preset_id',
        'duration_seconds',
        'generation_cost_usd',
        'idempotency_key',
        'metadata',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'generation_cost_usd' => 'decimal:4',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(AIModel::class, 'model_id');
    }

    public function preset(): BelongsTo
    {
        return $this->belongsTo(Preset::class, 'preset_id');
    }

    /**
     * @return \App\Domain\Credits\Database\Factories\CreditLedgerFactory
     */
    protected static function newFactory()
    {
        return \App\Domain\Credits\Database\Factories\CreditLedgerFactory::new();
    }
}
