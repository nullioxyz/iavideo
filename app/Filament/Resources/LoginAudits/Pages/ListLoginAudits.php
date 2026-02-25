<?php

namespace App\Filament\Resources\LoginAudits\Pages;

use App\Filament\Resources\LoginAudits\LoginAuditResource;
use Filament\Resources\Pages\ListRecords;

class ListLoginAudits extends ListRecords
{
    protected static string $resource = LoginAuditResource::class;
}

