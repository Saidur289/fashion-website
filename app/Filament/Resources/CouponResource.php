<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages\ManageCoupons;
use App\Models\Coupon;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-ticket';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.customers');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.coupons');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('code')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(40)
                ->dehydrateStateUsing(fn (?string $state) => strtoupper(trim((string) $state))),
            Select::make('type')
                ->options([
                    'percent' => 'Percentage (%)',
                    'fixed' => 'Fixed Amount (Tk)',
                ])
                ->default('percent')
                ->required(),
            TextInput::make('value')->label('Discount Value')->numeric()->required()->minValue(1),
            TextInput::make('min_order')->label('Min Order Amount (Tk)')->numeric()->default(0)->minValue(0),
            TextInput::make('max_discount')->label('Max Discount Cap (Tk, optional)')->numeric()->minValue(0),
            TextInput::make('usage_limit')->label('Max Total Uses (empty = unlimited)')->numeric()->minValue(1),
            DateTimePicker::make('starts_at')->label('Starts At (optional)'),
            DateTimePicker::make('expires_at')->label('Expires At (optional)'),
            Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('code')->searchable()->sortable()->weight('bold')->copyable(),
                TextColumn::make('label')->label('Discount')->state(fn (Coupon $r) => $r->label())->badge()->color('warning'),
                TextColumn::make('min_order')->label('Min Order')->formatStateUsing(fn ($state) => Product::taka((int) $state)),
                TextColumn::make('used')
                    ->label('Usage')
                    ->formatStateUsing(fn (Coupon $r) => $r->used.' / '.($r->usage_limit ?? '∞')),
                TextColumn::make('expires_at')->label('Expires')->dateTime('d M Y')->placeholder('Never'),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCoupons::route('/'),
        ];
    }
}
