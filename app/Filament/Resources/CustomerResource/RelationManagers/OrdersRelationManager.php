<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Customer Orders';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->label('Order #')->weight('bold'),
                TextColumn::make('total')->formatStateUsing(fn ($state) => Product::taka((int) $state)),
                TextColumn::make('payment_method')->formatStateUsing(fn (Order $r) => $r->paymentLabel()),
                TextColumn::make('status')->badge()->formatStateUsing(fn (string $state) => Order::STATUSES[$state] ?? ucfirst($state)),
                TextColumn::make('created_at')->dateTime('d M Y'),
            ])
            ->recordActions([
                Action::make('open')
                    ->label('View Order')
                    ->url(fn (Order $record) => OrderResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
