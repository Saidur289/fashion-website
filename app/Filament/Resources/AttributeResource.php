<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AttributeResource\Pages;
use App\Filament\Resources\AttributeResource\RelationManagers\ValuesRelationManager;
use App\Models\Attribute;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class AttributeResource extends Resource
{
    protected static ?string $model = Attribute::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-swatch';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.attributes');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(80)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (?string $state, Set $set, ?Attribute $record) => $record ? null : $set('slug', Str::slug((string) $state))),
            TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(80),
            Select::make('type')
                ->options([
                    'color' => 'Color (with swatch hex)',
                    'text' => 'Text (Size / Volume / Shade)',
                ])
                ->default('text')
                ->required(),
            TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('bold'),
                TextColumn::make('slug')->color('gray'),
                TextColumn::make('type')->badge()->color(fn (string $state) => $state === 'color' ? 'warning' : 'primary'),
                TextColumn::make('values_count')->counts('values')->label('Values')->badge(),
                TextColumn::make('sort_order')->label('Order')->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ValuesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAttributes::route('/'),
            'create' => Pages\CreateAttribute::route('/create'),
            'edit' => Pages\EditAttribute::route('/{record}/edit'),
        ];
    }
}
