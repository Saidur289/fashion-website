<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages\ListActivityLogs;
use App\Models\ActivityLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.activity');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('When')->dateTime('d M Y, h:i A')->sortable(),
                TextColumn::make('user.name')->label('Admin')->placeholder('System')->searchable(),
                TextColumn::make('action')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'created', 'restored', 'payment_verified' => 'success',
                        'deleted' => 'danger',
                        'status_changed', 'stock_adjusted' => 'warning',
                        default => 'primary',
                    }),
                TextColumn::make('subject_type')->label('Type')->placeholder('—'),
                TextColumn::make('subject_label')->label('Item')->placeholder('—')->searchable(),
                TextColumn::make('description')->wrap()->searchable(),
                TextColumn::make('ip')->label('IP')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('action')->options([
                    'created' => 'Created',
                    'updated' => 'Updated',
                    'deleted' => 'Deleted',
                    'restored' => 'Restored',
                    'status_changed' => 'Status Changed',
                    'payment_verified' => 'Payment Verified',
                    'stock_adjusted' => 'Stock Adjusted',
                    'settings_updated' => 'Settings Updated',
                ]),
                SelectFilter::make('subject_type')->options([
                    'Order' => 'Order',
                    'Product' => 'Product',
                    'Review' => 'Review',
                    'User' => 'User',
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
        ];
    }
}
