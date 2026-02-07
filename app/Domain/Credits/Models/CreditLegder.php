<?php

namespace App\Domain\Credits\Models;

use App\Domain\Auth\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditLegder extends EloquentModel
{
    use HasFactory;

    protected $table = 'credit_ledger';

    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'delta',
        'balance_after',
        'reason',
        'reference_type',
        'reference_id',
        'created_at',
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
        ];
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return \App\Domain\Credits\Database\Factories\CreditLedgerFactory
     */
    protected static function newFactory()
    {
        return \App\Domain\Credits\Database\Factories\CreditLedgerFactory::new();
    }
}
