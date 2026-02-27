<?php

namespace App\Domain\Seo\Tests\Integration;

use App\Domain\Languages\Models\Language;
use App\Domain\Seo\Models\Seo;
use App\Domain\Seo\Models\SeoLang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_localized_seo_for_country(): void
    {
        $en = Language::query()->create([
            'title' => 'English',
            'slug' => 'en',
            'is_default' => true,
            'active' => true,
        ]);

        $pt = Language::query()->create([
            'title' => 'Portuguese (BR)',
            'slug' => 'pt-BR',
            'is_default' => false,
            'active' => true,
        ]);

        $seo = Seo::query()->create([
            'slug' => 'home',
            'meta_title' => 'Home default',
            'meta_description' => 'Default description',
            'active' => true,
        ]);

        SeoLang::query()->create([
            'seo_id' => $seo->getKey(),
            'language_id' => $en->getKey(),
            'slug' => 'home-en',
            'meta_title' => 'Home EN',
            'meta_description' => 'Description EN',
        ]);

        SeoLang::query()->create([
            'seo_id' => $seo->getKey(),
            'language_id' => $pt->getKey(),
            'slug' => 'inicio',
            'meta_title' => 'Inicio PT',
            'meta_description' => 'Descricao PT',
        ]);

        $response = $this->withHeader('CF-IPCountry', 'BR')
            ->getJson('/api/seo/inicio');

        $response->assertOk();
        $response->assertJsonPath('data.id', $seo->getKey());
        $response->assertJsonPath('data.slug', 'inicio');
        $response->assertJsonPath('data.meta_title', 'Inicio PT');
        $response->assertJsonPath('data.meta_description', 'Descricao PT');
    }

    public function test_show_falls_back_to_default_language_and_returns_not_found_for_inactive(): void
    {
        $en = Language::query()->create([
            'title' => 'English',
            'slug' => 'en',
            'is_default' => true,
            'active' => true,
        ]);

        Language::query()->create([
            'title' => 'Italian',
            'slug' => 'it',
            'is_default' => false,
            'active' => true,
        ]);

        $seo = Seo::query()->create([
            'slug' => 'policy',
            'meta_title' => 'Policy default',
            'active' => true,
        ]);

        SeoLang::query()->create([
            'seo_id' => $seo->getKey(),
            'language_id' => $en->getKey(),
            'slug' => 'policy-en',
            'meta_title' => 'Policy EN',
        ]);

        $fallback = $this->withHeader('CF-IPCountry', 'IT')
            ->getJson('/api/seo/policy-en');
        $fallback->assertOk();
        $fallback->assertJsonPath('data.id', $seo->getKey());
        $fallback->assertJsonPath('data.meta_title', 'Policy EN');

        Seo::query()->create([
            'slug' => 'disabled',
            'active' => false,
        ]);

        $notFound = $this->withHeader('CF-IPCountry', 'BR')
            ->getJson('/api/seo/disabled');
        $notFound->assertNotFound();
    }

    public function test_show_uses_accept_language_when_country_header_is_missing(): void
    {
        $en = Language::query()->create([
            'title' => 'English',
            'slug' => 'en',
            'is_default' => true,
            'active' => true,
        ]);

        $pt = Language::query()->create([
            'title' => 'Portuguese (BR)',
            'slug' => 'pt-BR',
            'is_default' => false,
            'active' => true,
        ]);

        $seo = Seo::query()->create([
            'slug' => 'home',
            'meta_title' => 'Home default',
            'active' => true,
        ]);

        SeoLang::query()->create([
            'seo_id' => $seo->getKey(),
            'language_id' => $en->getKey(),
            'slug' => 'home-en',
            'meta_title' => 'Home EN',
        ]);

        SeoLang::query()->create([
            'seo_id' => $seo->getKey(),
            'language_id' => $pt->getKey(),
            'slug' => 'inicio',
            'meta_title' => 'Inicio PT',
        ]);

        $response = $this->withHeader('Accept-Language', 'pt-BR,pt;q=0.9,en;q=0.8')
            ->getJson('/api/seo/home-en');

        $response->assertOk();
        $response->assertJsonPath('data.id', $seo->getKey());
        $response->assertJsonPath('data.slug', 'inicio');
        $response->assertJsonPath('data.meta_title', 'Inicio PT');
    }
}
