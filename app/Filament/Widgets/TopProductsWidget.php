<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TopProductsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Best Selling Products')
            ->query(Product::query()->with('category')->orderByDesc('sold_count')->limit(8))
            ->paginated(false)
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->disk('public_root')
                    ->size(36)
                    ->circular(),
                TextColumn::make('name')
                    ->limit(32)
                    ->description(fn (Product $r) => $r->category?->name),
                TextColumn::make('price')
                    ->formatStateUsing(fn ($state) => Product::taka((int) $state)),
                TextColumn::make('sold_count')
                    ->label('Sold')
                    ->badge()
                    ->color('success'),
            ]);
    }
}
