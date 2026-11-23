<?php

namespace App\Filament\Resources\AttributeResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class ValuesRelationManager extends RelationManager
{
    protected static string $relationship = 'values';

    protected static ?string $title = 'Attribute Values';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('value')
                ->required()
                ->maxLength(80)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (?string $state, Set $set) => $set('slug', Str::slug((string) $state))),
            TextInput::make('slug')->required()->maxLength(80),
            ColorPicker::make('color')->label('Swatch Hex Color (for Color attribute)'),
            TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ColorColumn::make('color')->label('Swatch'),
                TextColumn::make('value')->weight('bold'),
                TextColumn::make('slug')->color('gray'),
                TextColumn::make('sort_order')->label('Order'),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
