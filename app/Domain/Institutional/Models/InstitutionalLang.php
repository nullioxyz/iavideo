<?php

namespace App\Domain\Institutional\Models;

use App\Domain\Languages\Models\Language;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstitutionalLang extends EloquentModel
{
    protected $table = 'institutional_langs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'institutional_id',
        'language_id',
        'title',
        'subtitle',
        'short_description',
        'description',
        'slug',
    ];

    public function institutional(): BelongsTo
    {
        return $this->belongsTo(Institutional::class, 'institutional_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}

