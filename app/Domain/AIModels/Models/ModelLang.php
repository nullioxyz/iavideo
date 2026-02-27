<?php

namespace App\Domain\AIModels\Models;

use App\Domain\Languages\Models\Language;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelLang extends EloquentModel
{
    protected $table = 'model_langs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'model_id',
        'language_id',
        'name',
        'slug',
    ];

    public function model(): BelongsTo
    {
        return $this->belongsTo(Model::class, 'model_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}

