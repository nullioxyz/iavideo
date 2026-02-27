<?php

namespace Database\Seeders;

use App\Domain\Languages\Models\Language;
use Illuminate\Database\Seeder;

class LanguagesSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['title' => 'English', 'slug' => 'en', 'is_default' => true, 'active' => true],
            ['title' => 'Português (Brasil)', 'slug' => 'pt-BR', 'is_default' => false, 'active' => true],
            ['title' => 'Italiano', 'slug' => 'it', 'is_default' => false, 'active' => true],
        ];

        foreach ($languages as $language) {
            Language::query()->updateOrCreate(
                ['slug' => $language['slug']],
                $language
            );
        }
    }
}

