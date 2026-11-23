<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HomeSectionResource\Pages\ManageHomeSections;
use App\Models\HomeSection;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class HomeSectionResource extends Resource
{
    protected static ?string $model = HomeSection::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-view-columns';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.storefront');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.home_sections');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(120),
            TextInput::make('key')->disabled(),
            TextInput::make('eyebrow')->label('Small Eyebrow Label')->maxLength(120),
            TextInput::make('title')->label('Section Heading')->maxLength(180),
            Textarea::make('subtitle')->label('Subtitle / Copy')->rows(3)->columnSpanFull(),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->label('Show on Homepage')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->afterReordering(fn () => \App\Services\StorefrontLayout::clearCache())
            ->paginated(false)
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('name')->weight('bold')->description(fn (HomeSection $r) => $r->key),
                TextColumn::make('title')->placeholder('(Default heading)')->limit(40),
                ToggleColumn::make('is_active')->label('Visible'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageHomeSections::route('/')];
    }
}
