<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MenuResource\Pages\ManageMenus;
use App\Models\Menu;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-bars-3';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.storefront');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.menus');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('location')->options(Menu::LOCATIONS)->default('header')->required(),
            TextInput::make('label')->required()->maxLength(80),
            TextInput::make('url')->required()->maxLength(255)->placeholder('/shop or https://…'),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('open_in_new_tab')->label('Open in New Tab')->default(false),
            Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->afterReordering(fn () => \App\Services\StorefrontLayout::clearCache())
            ->columns([
                TextColumn::make('location')->badge()->formatStateUsing(fn (string $state) => Menu::LOCATIONS[$state] ?? $state)->color('primary'),
                TextColumn::make('label')->weight('bold')->searchable(),
                TextColumn::make('url')->color('gray'),
                TextColumn::make('sort_order')->label('Order')->sortable(),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->filters([
                SelectFilter::make('location')->options(Menu::LOCATIONS),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageMenus::route('/')];
    }
}
