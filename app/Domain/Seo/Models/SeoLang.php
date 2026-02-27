<?php

namespace App\Domain\Seo\Models;

use App\Domain\Languages\Models\Language;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoLang extends EloquentModel
{
    protected $table = 'seo_langs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'seo_id',
        'language_id',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'twitter_title',
        'twitter_description',
        'slug',
    ];

    public function seo(): BelongsTo
    {
        return $this->belongsTo(Seo::class, 'seo_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}

