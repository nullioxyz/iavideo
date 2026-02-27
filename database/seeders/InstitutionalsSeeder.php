<?php

namespace Database\Seeders;

use App\Domain\Institutional\Models\Institutional;
use App\Domain\Languages\Models\Language;
use Illuminate\Database\Seeder;

class InstitutionalsSeeder extends Seeder
{
    public function run(): void
    {
        $languages = Language::query()
            ->whereIn('slug', ['en', 'pt-BR', 'it'])
            ->get()
            ->keyBy('slug');

        $items = [
            ['slug' => 'about', 'title' => 'About'],
            ['slug' => 'contact-us', 'title' => 'Contact Us'],
            ['slug' => 'faq', 'title' => 'FAQ'],
            ['slug' => 'tutorial', 'title' => 'Tutorial'],
            ['slug' => 'terms-and-conditions', 'title' => 'Terms and Conditions'],
            ['slug' => 'privacy-policy', 'title' => 'Privacy Policy'],
            ['slug' => 'refund-policy', 'title' => 'Refund Policy'],
            ['slug' => 'gdpr-compliance', 'title' => 'GDPR Compliance'],
            ['slug' => 'initial-page-text', 'title' => 'Initial Page Text'],
            ['slug' => 'footer-text', 'title' => 'Footer Text'],
        ];

        foreach ($items as $item) {
            $institutional = Institutional::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $item['title'],
                    'subtitle' => null,
                    'short_description' => $item['title'].' content.',
                    'description' => 'Institutional content for '.$item['title'].'.',
                    'active' => true,
                ]
            );

            $pt = $languages->get('pt-BR');
            if ($pt) {
                $institutional->translations()->updateOrCreate(
                    ['language_id' => (int) $pt->getKey()],
                    [
                        'title' => $this->translateToPt($item['title']),
                        'subtitle' => null,
                        'short_description' => 'Conteudo institucional de '.$this->translateToPt($item['title']).'.',
                        'description' => 'Descricao institucional de '.$this->translateToPt($item['title']).'.',
                        'slug' => $item['slug'].'-pt',
                    ]
                );
            }

            $it = $languages->get('it');
            if ($it) {
                $institutional->translations()->updateOrCreate(
                    ['language_id' => (int) $it->getKey()],
                    [
                        'title' => $this->translateToIt($item['title']),
                        'subtitle' => null,
                        'short_description' => 'Contenuto istituzionale per '.$this->translateToIt($item['title']).'.',
                        'description' => 'Descrizione istituzionale per '.$this->translateToIt($item['title']).'.',
                        'slug' => $item['slug'].'-it',
                    ]
                );
            }
        }
    }

    private function translateToPt(string $value): string
    {
        return match ($value) {
            'About' => 'Sobre',
            'Contact Us' => 'Fale Conosco',
            'FAQ' => 'Perguntas Frequentes',
            'Tutorial' => 'Tutorial',
            'Terms and Conditions' => 'Termos e Condicoes',
            'Privacy Policy' => 'Politica de Privacidade',
            'Refund Policy' => 'Politica de Reembolso',
            'GDPR Compliance' => 'Conformidade GDPR',
            'Initial Page Text' => 'Texto Inicial da Pagina',
            'Footer Text' => 'Texto do Rodape',
            default => $value,
        };
    }

    private function translateToIt(string $value): string
    {
        return match ($value) {
            'About' => 'Chi Siamo',
            'Contact Us' => 'Contattaci',
            'FAQ' => 'Domande Frequenti',
            'Tutorial' => 'Tutorial',
            'Terms and Conditions' => 'Termini e Condizioni',
            'Privacy Policy' => 'Informativa sulla Privacy',
            'Refund Policy' => 'Politica di Rimborso',
            'GDPR Compliance' => 'Conformita GDPR',
            'Initial Page Text' => 'Testo Iniziale della Pagina',
            'Footer Text' => 'Testo del Footer',
            default => $value,
        };
    }
}

