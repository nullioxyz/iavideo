<?php

namespace App\Domain\AIModels\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PresetsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'aspect_ratio' => ['nullable', 'in:16:9,9:16,1:1'],
            'tag' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $tags = $this->input('tags');

        if (is_string($tags)) {
            $tags = array_filter(array_map('trim', explode(',', $tags)));
        }

        if (! is_array($tags)) {
            $tags = [];
        }

        $singleTag = $this->input('tag') ?? $this->input('category');
        if (is_string($singleTag) && trim($singleTag) !== '') {
            $tags[] = trim($singleTag);
        }

        $tags = array_values(array_unique(array_filter(array_map(
            static fn ($item) => is_string($item) ? trim($item) : '',
            $tags
        ))));

        $this->merge([
            'tags' => $tags,
        ]);
    }

    /**
     * @return array{aspect_ratio:?string,tags:list<string>}
     */
    public function filters(): array
    {
        $aspectRatio = $this->input('aspect_ratio');
        $tags = $this->input('tags', []);

        return [
            'aspect_ratio' => is_string($aspectRatio) ? $aspectRatio : null,
            'tags' => is_array($tags) ? array_values($tags) : [],
        ];
    }
}
