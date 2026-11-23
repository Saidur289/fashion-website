<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingZoneResource\Pages\ManageShippingZones;
use App\Models\Product;
use App\Models\ShippingZone;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ShippingZoneResource extends Resource
{
    protected static ?string $model = ShippingZone::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-truck';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.customers');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.shipping');
    }

    public static function form(Schema $schema): Schema
    {
        $districts = array_combine(config('districts'), config('districts'));

        return $schema->components([
            TextInput::make('name')->label('Zone Name')->required()->maxLength(100)->placeholder('Inside Dhaka / Sub-Dhaka / Outside Dhaka'),
            TextInput::make('charge')->label('Delivery Charge (Tk)')->numeric()->required()->minValue(0),
            Select::make('districts')
                ->label('Districts (leave empty to match all remaining districts)')
                ->options($districts)
                ->multiple()
                ->searchable()
                ->columnSpanFull(),
            TextInput::make('estimated_days')->label('Estimated Delivery')->placeholder('1–2 days'),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')->weight('bold')->searchable(),
                TextColumn::make('districts')
                    ->label('Districts')
                    ->formatStateUsing(fn ($state) => empty($state) ? 'All other districts (Fallback)' : implode(', ', (array) $state))
                    ->wrap(),
                TextColumn::make('charge')->formatStateUsing(fn ($state) => Product::taka((int) $state))->badge()->color('primary'),
                TextColumn::make('estimated_days')->label('ETA')->placeholder('—'),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageShippingZones::route('/'),
        ];
    }
}
