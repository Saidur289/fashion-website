<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    protected static ?string $title = 'Saved Addresses';

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                TextColumn::make('label')->placeholder('—')->badge(),
                TextColumn::make('name')->weight('bold'),
                TextColumn::make('phone'),
                TextColumn::make('district'),
                TextColumn::make('area')->placeholder('—'),
                TextColumn::make('address')->wrap(),
                IconColumn::make('is_default')->label('Default')->boolean(),
            ]);
    }
}
