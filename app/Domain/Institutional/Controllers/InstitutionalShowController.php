<?php

namespace App\Domain\Institutional\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Institutional\Resources\InstitutionalResource;
use App\Domain\Institutional\UseCases\GetInstitutionalBySlugUseCase;
use App\Domain\Languages\Support\CountryLanguageContextResolver;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InstitutionalShowController extends Controller
{
    public function __construct(
        private readonly GetInstitutionalBySlugUseCase $useCase,
        private readonly CountryLanguageContextResolver $languageContextResolver,
    ) {}

    public function __invoke(Request $request, string $slug): InstitutionalResource
    {
        $user = auth('api')->user();
        $context = $this->languageContextResolver->fromRequest(
            $request,
            $user instanceof User ? $user : null
        );

        $request->attributes->set('preferred_language_id', $context['preferred_language_id']);
        $request->attributes->set('default_language_id', $context['default_language_id']);

        $item = $this->useCase->execute(
            $slug,
            $context['preferred_language_id'],
            $context['default_language_id'],
        );

        if (! $item) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return new InstitutionalResource($item);
    }
}

