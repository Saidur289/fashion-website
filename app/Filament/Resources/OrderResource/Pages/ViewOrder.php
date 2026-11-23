<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('invoice')
                ->label('Print / PDF Invoice')
                ->icon('heroicon-o-printer')
                ->color('warning')
                ->url(fn () => route('admin.orders.invoice', $this->record))
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }
}
