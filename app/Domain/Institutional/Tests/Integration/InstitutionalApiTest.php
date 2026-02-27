<?php

namespace App\Domain\Institutional\Tests\Integration;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Tests\Traits\AuthenticatesWithJwt;
use App\Domain\Institutional\Models\Institutional;
use App\Domain\Institutional\Models\InstitutionalLang;
use App\Domain\Languages\Models\Language;
use Database\Seeders\InstitutionalsSeeder;
use Database\Seeders\LanguagesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstitutionalApiTest extends TestCase
{
    use AuthenticatesWithJwt;
    use RefreshDatabase;

    public function test_list_returns_only_active_institutionals_with_country_translation(): void
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

        $about = Institutional::query()->create([
            'title' => 'About',
            'subtitle' => 'About subtitle',
            'short_description' => 'About short',
            'description' => 'About description',
            'slug' => 'about',
            'active' => true,
        ]);

        InstitutionalLang::query()->create([
            'institutional_id' => $about->getKey(),
            'language_id' => $pt->getKey(),
            'title' => 'Sobre',
            'subtitle' => 'Subtitulo',
            'short_description' => 'Curta descricao',
            'description' => 'Descricao completa',
            'slug' => 'sobre',
        ]);

        Institutional::query()->create([
            'title' => 'Hidden',
            'slug' => 'hidden',
            'active' => false,
        ]);

        $response = $this->withHeader('CF-IPCountry', 'BR')
            ->getJson('/api/institutional');

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $about->getKey());
        $response->assertJsonPath('data.0.title', 'Sobre');
        $response->assertJsonPath('data.0.slug', 'sobre');
        $response->assertJsonPath('data.0.active', true);
    }

    public function test_show_resolves_by_translated_slug_and_falls_back_to_default_language(): void
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

        $it = Language::query()->create([
            'title' => 'Italian',
            'slug' => 'it',
            'is_default' => false,
            'active' => true,
        ]);

        $institutional = Institutional::query()->create([
            'title' => 'About fallback',
            'slug' => 'about-fallback',
            'active' => true,
        ]);

        InstitutionalLang::query()->create([
            'institutional_id' => $institutional->getKey(),
            'language_id' => $en->getKey(),
            'title' => 'About EN',
            'slug' => 'about-en',
        ]);

        InstitutionalLang::query()->create([
            'institutional_id' => $institutional->getKey(),
            'language_id' => $pt->getKey(),
            'title' => 'Sobre PT',
            'slug' => 'sobre-pt',
        ]);

        $byTranslatedSlug = $this->withHeader('CF-IPCountry', 'BR')
            ->getJson('/api/institutional/sobre-pt');
        $byTranslatedSlug->assertOk();
        $byTranslatedSlug->assertJsonPath('data.id', $institutional->getKey());
        $byTranslatedSlug->assertJsonPath('data.title', 'Sobre PT');
        $byTranslatedSlug->assertJsonPath('data.slug', 'sobre-pt');

        $fallbackResponse = $this->withHeader('CF-IPCountry', 'IT')
            ->getJson('/api/institutional/about-en');
        $fallbackResponse->assertOk();
        $fallbackResponse->assertJsonPath('data.id', $institutional->getKey());
        $fallbackResponse->assertJsonPath('data.title', 'About EN');
        $fallbackResponse->assertJsonPath('data.slug', 'about-en');
    }

    public function test_show_uses_accept_language_when_country_is_not_available(): void
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

        $institutional = Institutional::query()->create([
            'title' => 'About',
            'slug' => 'about',
            'active' => true,
        ]);

        InstitutionalLang::query()->create([
            'institutional_id' => $institutional->getKey(),
            'language_id' => $en->getKey(),
            'title' => 'About EN',
            'slug' => 'about-en',
        ]);

        InstitutionalLang::query()->create([
            'institutional_id' => $institutional->getKey(),
            'language_id' => $pt->getKey(),
            'title' => 'Sobre PT',
            'slug' => 'sobre-pt',
        ]);

        $response = $this->withHeader('Accept-Language', 'pt-BR,pt;q=0.9,en;q=0.8')
            ->getJson('/api/institutional/about-en');

        $response->assertOk();
        $response->assertJsonPath('data.id', $institutional->getKey());
        $response->assertJsonPath('data.title', 'Sobre PT');
        $response->assertJsonPath('data.slug', 'sobre-pt');
    }

    public function test_show_prefers_authenticated_user_language_over_headers(): void
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

        $it = Language::query()->create([
            'title' => 'Italian',
            'slug' => 'it',
            'is_default' => false,
            'active' => true,
        ]);

        $user = User::factory()->create([
            'active' => true,
            'password' => bcrypt('password'),
            'language_id' => $it->getKey(),
        ]);

        $token = $this->loginAndGetToken($user);

        $institutional = Institutional::query()->create([
            'title' => 'About',
            'slug' => 'about',
            'active' => true,
        ]);

        InstitutionalLang::query()->create([
            'institutional_id' => $institutional->getKey(),
            'language_id' => $en->getKey(),
            'title' => 'About EN',
            'slug' => 'about-en',
        ]);

        InstitutionalLang::query()->create([
            'institutional_id' => $institutional->getKey(),
            'language_id' => $it->getKey(),
            'title' => 'Chi Siamo IT',
            'slug' => 'chi-siamo-it',
        ]);

        $response = $this->withJwt($token)
            ->withHeader('CF-IPCountry', 'BR')
            ->withHeader('Accept-Language', 'pt-BR,pt;q=0.9,en;q=0.8')
            ->getJson('/api/institutional/about-en');

        $response->assertOk();
        $response->assertJsonPath('data.id', $institutional->getKey());
        $response->assertJsonPath('data.title', 'Chi Siamo IT');
        $response->assertJsonPath('data.slug', 'chi-siamo-it');
    }

    public function test_institutionals_seeder_keeps_initial_page_text_slug(): void
    {
        $this->seed(LanguagesSeeder::class);
        $this->seed(InstitutionalsSeeder::class);

        $this->assertDatabaseHas('institutionals', [
            'slug' => 'initial-page-text',
            'active' => true,
        ]);
    }
}
