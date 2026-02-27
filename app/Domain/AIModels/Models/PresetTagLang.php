<?php

namespace App\Domain\AIModels\Models;

use App\Domain\Languages\Models\Language;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresetTagLang extends EloquentModel
{
    protected $table = 'preset_tag_langs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'preset_tag_id',
        'language_id',
        'name',
        'slug',
    ];

    public function tag(): BelongsTo
    {
        return $this->belongsTo(PresetTag::class, 'preset_tag_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}

