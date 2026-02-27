<?php

namespace App\Filament\Support;

use App\Domain\Languages\Models\Language;
use Illuminate\Database\Eloquent\Model;

trait SyncsLanguageTranslations
{
    /**
     * @return array<string, array<string, mixed>>
     */
    protected function pullTranslationsPayload(array &$data): array
    {
        $payload = $data['translations_payload'] ?? [];
        unset($data['translations_payload']);

        return is_array($payload) ? $payload : [];
    }

    /**
     * @param  list<string>  $fields
     */
    protected function syncTranslations(Model $record, array $payload, array $fields): void
    {
        $languages = Language::query()
            ->whereIn('slug', array_keys($payload))
            ->get()
            ->keyBy('slug');

        foreach ($payload as $slug => $values) {
            if (! is_array($values)) {
                continue;
            }

            $language = $languages->get($slug);
            if (! $language) {
                continue;
            }

            $valuesToPersist = [];
            foreach ($fields as $field) {
                $raw = $values[$field] ?? null;
                $valuesToPersist[$field] = is_string($raw) ? trim($raw) : $raw;
            }

            $isEmpty = true;
            foreach ($fields as $field) {
                if (filled($valuesToPersist[$field] ?? null)) {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                $record->translations()
                    ->where('language_id', (int) $language->getKey())
                    ->delete();

                continue;
            }

            $record->translations()->updateOrCreate(
                ['language_id' => (int) $language->getKey()],
                $valuesToPersist
            );
        }
    }

    /**
     * @param  list<string>  $fields
     * @return array<string, array<string, mixed>>
     */
    protected function buildTranslationsPayload(Model $record, array $fields): array
    {
        $languages = Language::query()
            ->where('active', true)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();

        $translations = $record->translations()->get()->keyBy('language_id');
        $payload = [];

        foreach ($languages as $language) {
            $entry = [];
            $translation = $translations->get((int) $language->getKey());

            foreach ($fields as $field) {
                $entry[$field] = $translation?->{$field};
            }

            $payload[(string) $language->slug] = $entry;
        }

        return $payload;
    }
}

