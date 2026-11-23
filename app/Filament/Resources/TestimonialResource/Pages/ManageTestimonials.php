<?php

namespace App\Filament\Resources\TestimonialResource\Pages;

use App\Filament\Resources\TestimonialResource;
use App\Models\Testimonial;
use App\Services\ImageOptimizer;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTestimonials extends ManageRecords
{
    protected static string $resource = TestimonialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->after(function (Testimonial $record): void {
                if ($record->photo && ($webp = ImageOptimizer::toWebp($record->photo)) !== $record->photo) {
                    $record->updateQuietly(['photo' => $webp]);
                }
            }),
        ];
    }
}
