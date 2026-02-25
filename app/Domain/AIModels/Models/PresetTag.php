<?php

namespace App\Domain\AIModels\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class PresetTag extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function presets(): BelongsToMany
    {
        return $this->belongsToMany(
            Preset::class,
            'preset_tag_preset',
            'preset_tag_id',
            'preset_id'
        );
    }

    protected static function booted(): void
    {
        static::saving(function (PresetTag $tag): void {
            if (! is_string($tag->slug) || $tag->slug === '') {
                $tag->slug = Str::slug((string) $tag->name);
            }
        });
    }

    /**
     * @return \App\Domain\AIModels\Database\Factories\PresetTagFactory
     */
    protected static function newFactory()
    {
        return \App\Domain\AIModels\Database\Factories\PresetTagFactory::new();
    }
}
