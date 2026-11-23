<?php

namespace App\Filament\Resources\TeamMemberResource\Pages;

use App\Filament\Resources\TeamMemberResource;
use App\Models\TeamMember;
use App\Services\ImageOptimizer;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTeamMembers extends ManageRecords
{
    protected static string $resource = TeamMemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->after(function (TeamMember $record): void {
                if ($record->photo && ($webp = ImageOptimizer::toWebp($record->photo)) !== $record->photo) {
                    $record->updateQuietly(['photo' => $webp]);
                }
            }),
        ];
    }
}
