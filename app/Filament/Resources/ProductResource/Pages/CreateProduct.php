<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\ActivityLog;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        ProductResource::optimizeProductImages($this->record);
        ActivityLog::record('created', "Created product “{$this->record->name}”", $this->record);
    }
}
