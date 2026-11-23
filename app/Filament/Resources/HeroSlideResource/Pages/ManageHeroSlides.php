<?php

namespace App\Filament\Resources\HeroSlideResource\Pages;

use App\Filament\Resources\HeroSlideResource;
use App\Models\HeroSlide;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageHeroSlides extends ManageRecords
{
    protected static string $resource = HeroSlideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->after(fn (HeroSlide $record) => HeroSlideResource::optimizeImages($record)),
        ];
    }
}
