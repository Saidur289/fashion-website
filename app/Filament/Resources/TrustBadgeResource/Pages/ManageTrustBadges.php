<?php

namespace App\Filament\Resources\TrustBadgeResource\Pages;

use App\Filament\Resources\TrustBadgeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTrustBadges extends ManageRecords
{
    protected static string $resource = TrustBadgeResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
