<?php

namespace App\Filament\Resources\ShippingZoneResource\Pages;

use App\Filament\Resources\ShippingZoneResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageShippingZones extends ManageRecords
{
    protected static string $resource = ShippingZoneResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
