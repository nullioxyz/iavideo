<?php

namespace App\Domain\Seo\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Languages\Support\CountryLanguageContextResolver;
use App\Domain\Seo\Resources\SeoResource;
use App\Domain\Seo\UseCases\GetSeoBySlugUseCase;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SeoShowController extends Controller
{
    public function __construct(
        private readonly GetSeoBySlugUseCase $useCase,
        private readonly CountryLanguageContextResolver $languageContextResolver,
    ) {}

    public function __invoke(Request $request, string $slug): SeoResource
    {
        $user = auth('api')->user();
        $context = $this->languageContextResolver->fromRequest(
            $request,
            $user instanceof User ? $user : null
        );

        $request->attributes->set('preferred_language_id', $context['preferred_language_id']);
        $request->attributes->set('default_language_id', $context['default_language_id']);

        $seo = $this->useCase->execute(
            $slug,
            $context['preferred_language_id'],
            $context['default_language_id'],
        );

        if (! $seo) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return new SeoResource($seo);
    }
}

