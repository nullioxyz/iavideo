<?php

namespace App\Domain\Languages\Support;

use App\Domain\Auth\Models\User;
use App\Domain\Languages\Models\Language;
use Illuminate\Http\Request;

class UserLanguageContextResolver
{
    /**
     * @return array{preferred_language_id:?int,default_language_id:?int,preferred_language_slug:?string,default_language_slug:?string}
     */
    public function fromRequest(Request $request, ?User $user = null): array
    {
        $cached = $request->attributes->get('language_context');
        if (is_array($cached)) {
            return $cached;
        }

        if (! $user instanceof User) {
            $authUser = auth('api')->user();
            $user = $authUser instanceof User ? $authUser : null;
        }

        $resolved = $this->resolve($user);
        $request->attributes->set('language_context', $resolved);

        return $resolved;
    }

    /**
     * @return array{preferred_language_id:?int,default_language_id:?int,preferred_language_slug:?string,default_language_slug:?string}
     */
    public function resolve(?User $user = null): array
    {
        $default = Language::query()
            ->where('active', true)
            ->where('is_default', true)
            ->first()
            ?? Language::query()->where('active', true)->orderBy('id')->first();

        $preferredLanguageId = null;
        $preferredLanguageSlug = null;

        if ($user instanceof User && $user->language_id) {
            $preferred = Language::query()
                ->whereKey((int) $user->language_id)
                ->where('active', true)
                ->first();

            if ($preferred) {
                $preferredLanguageId = (int) $preferred->getKey();
                $preferredLanguageSlug = (string) $preferred->slug;
            }
        }

        return [
            'preferred_language_id' => $preferredLanguageId,
            'default_language_id' => $default ? (int) $default->getKey() : null,
            'preferred_language_slug' => $preferredLanguageSlug,
            'default_language_slug' => $default ? (string) $default->slug : null,
        ];
    }
}
