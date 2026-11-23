<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockProductsWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Low Stock Products (≤ 5)')
            ->query(
                Product::query()
                    ->whereNotNull('stock')
                    ->where('stock', '<=', 5)
                    ->orderBy('stock')
                    ->limit(8)
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('name')->limit(34),
                TextColumn::make('sku')->placeholder('—'),
                TextColumn::make('stock')
                    ->badge()
                    ->color(fn ($state) => $state <= 0 ? 'danger' : 'warning'),
            ])
            ->emptyStateHeading('All products are well stocked!');
    }
}
