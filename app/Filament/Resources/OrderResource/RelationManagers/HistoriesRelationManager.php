<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Order;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'histories';

    protected static ?string $title = 'Status & Change History';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->paginated(false)
            ->columns([
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Order::STATUSES[$state] ?? ucfirst($state)),
                TextColumn::make('note')->wrap(),
                TextColumn::make('changer.name')->label('Changed By')->placeholder('System / Customer'),
                TextColumn::make('created_at')->label('Time')->dateTime('d M Y, h:i A'),
            ]);
    }
}
