<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use App\Models\Product;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'Order Items';

    public function table(Table $table): Table
    {
        return $table
            ->paginated(false)
            ->columns([
                ImageColumn::make('image')->label('')->size(40),
                TextColumn::make('name')->weight('bold'),
                TextColumn::make('variant')->placeholder('—'),
                TextColumn::make('sku')->placeholder('—'),
                TextColumn::make('price')->formatStateUsing(fn ($state) => Product::taka((int) $state)),
                TextColumn::make('qty')->badge()->color('primary'),
                TextColumn::make('total')->weight('bold')->formatStateUsing(fn ($state) => Product::taka((int) $state)),
            ]);
    }
}
