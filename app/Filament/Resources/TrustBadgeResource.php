<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TrustBadgeResource\Pages\ManageTrustBadges;
use App\Models\TrustBadge;
use BackedEnum;
use Filament\Actions\DeleteAction;
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

class TrustBadgeResource extends Resource
{
    protected static ?string $model = TrustBadge::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 6;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.storefront');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.trust_badges');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('location')->options(TrustBadge::LOCATIONS)->default('product')->required(),
            Select::make('icon')->options(TrustBadge::ICONS)->default('shield')->required(),
            TextInput::make('title')->required()->maxLength(120),
            TextInput::make('subtitle')->maxLength(200)->placeholder('Shown on Homepage Why Shop cards'),
            TextInput::make('sort_order')->numeric()->default(0),
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
                TextColumn::make('location')->badge()->formatStateUsing(fn (string $state) => TrustBadge::LOCATIONS[$state] ?? $state)->color('primary'),
                TextColumn::make('icon')->badge()->color('warning'),
                TextColumn::make('title')->weight('bold'),
                TextColumn::make('subtitle')->placeholder('—')->limit(45),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->filters([
                SelectFilter::make('location')->options(TrustBadge::LOCATIONS),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageTrustBadges::route('/')];
    }
}
