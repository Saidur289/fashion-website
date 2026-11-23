<?php

namespace App\Filament\Resources\BrandResource\Pages;

use App\Filament\Resources\BrandResource;
use App\Models\Brand;
use App\Services\ImageOptimizer;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageBrands extends ManageRecords
{
    protected static string $resource = BrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->after(function (Brand $record): void {
                if ($record->logo && ($webp = ImageOptimizer::toWebp($record->logo)) !== $record->logo) {
                    $record->updateQuietly(['logo' => $webp]);
                }
            }),
        ];
    }
}
