<?php

namespace App\Domain\Institutional\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Institutional\Resources\InstitutionalResource;
use App\Domain\Institutional\UseCases\ListInstitutionalsUseCase;
use App\Domain\Languages\Support\CountryLanguageContextResolver;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;

class InstitutionalListController extends Controller
{
    public function __construct(
        private readonly ListInstitutionalsUseCase $useCase,
        private readonly CountryLanguageContextResolver $languageContextResolver,
    ) {}

    public function __invoke(Request $request): AnonymousResourceCollection
    {
        $user = auth('api')->user();
        $context = $this->languageContextResolver->fromRequest(
            $request,
            $user instanceof User ? $user : null
        );

        $request->attributes->set('preferred_language_id', $context['preferred_language_id']);
        $request->attributes->set('default_language_id', $context['default_language_id']);

        $items = $this->useCase->execute(
            $context['preferred_language_id'],
            $context['default_language_id'],
        );

        return InstitutionalResource::collection($items);
    }
}

