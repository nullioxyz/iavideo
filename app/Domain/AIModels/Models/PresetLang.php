<?php

namespace App\Domain\AIModels\Models;

use App\Domain\Languages\Models\Language;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresetLang extends EloquentModel
{
    protected $table = 'preset_langs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'preset_id',
        'language_id',
        'name',
        'prompt',
        'negative_prompt',
        'slug',
    ];

    public function preset(): BelongsTo
    {
        return $this->belongsTo(Preset::class, 'preset_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}

