<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages\ManageBrands;
use App\Models\Brand;
use App\Services\ImageOptimizer;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.brands');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(120)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (?string $state, Set $set, ?Brand $record) => $record ? null : $set('slug', Str::slug((string) $state))),
            TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(120),
            TextInput::make('sort_order')->numeric()->default(0),
            FileUpload::make('logo')
                ->disk('public_root')
                ->directory('images/uploads/brands')
                ->image()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('logo')->label('')->disk('public_root')->height(28),
                TextColumn::make('name')->searchable()->sortable()->weight('bold'),
                TextColumn::make('slug')->color('gray'),
                TextColumn::make('products_count')->counts('products')->label('Products')->badge(),
                TextColumn::make('sort_order')->label('Order')->sortable(),
            ])
            ->recordActions([
                EditAction::make()->after(function (Brand $record): void {
                    if ($record->logo && ($webp = ImageOptimizer::toWebp($record->logo)) !== $record->logo) {
                        $record->updateQuietly(['logo' => $webp]);
                    }
                }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBrands::route('/'),
        ];
    }
}
