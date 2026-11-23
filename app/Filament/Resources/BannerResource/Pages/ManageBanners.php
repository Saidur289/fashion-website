<?php

namespace App\Filament\Resources\BannerResource\Pages;

use App\Filament\Resources\BannerResource;
use App\Models\Banner;
use App\Services\ImageOptimizer;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageBanners extends ManageRecords
{
    protected static string $resource = BannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->after(function (Banner $record): void {
                if ($record->image && ($webp = ImageOptimizer::toWebp($record->image)) !== $record->image) {
                    $record->updateQuietly(['image' => $webp]);
                }
            }),
        ];
    }
}
