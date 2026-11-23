<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use App\Models\Category;
use App\Services\ImageOptimizer;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageCategories extends ManageRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->after(function (Category $record): void {
                if ($record->image && ($webp = ImageOptimizer::toWebp($record->image)) !== $record->image) {
                    $record->updateQuietly(['image' => $webp]);
                }
            }),
        ];
    }
}
