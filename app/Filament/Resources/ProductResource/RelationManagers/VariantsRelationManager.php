<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ImageOptimizer;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Variations (Color / Size)';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('values')
                ->label('Option Values (e.g. Color / Size)')
                ->relationship('values', 'value')
                ->getOptionLabelFromRecordUsing(fn ($record) => ($record->attribute?->name ? $record->attribute->name.': ' : '').$record->value)
                ->multiple()
                ->preload()
                ->searchable()
                ->required()
                ->columnSpanFull(),
            TextInput::make('sku')->label('Variant SKU')->maxLength(80),
            TextInput::make('price')->label('Price (Tk)')->numeric()->required()->minValue(0),
            TextInput::make('old_price')->label('Old Price (Tk)')->numeric()->minValue(0),
            TextInput::make('stock')->label('Stock (empty = unlimited)')->numeric()->minValue(0),
            TextInput::make('sort_order')->numeric()->default(0),
            FileUpload::make('image')
                ->label('Variant Image (optional)')
                ->disk('public_root')
                ->directory('images/uploads/variants')
                ->image(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image')->label('')->disk('public_root')->size(38)->square(),
                TextColumn::make('label')->label('Option')->state(fn (ProductVariant $r) => $r->label() ?: '—')->weight('bold'),
                TextColumn::make('sku')->placeholder('—'),
                TextColumn::make('price')->formatStateUsing(fn ($state) => Product::taka((int) $state)),
                TextColumn::make('old_price')->formatStateUsing(fn ($state) => $state ? Product::taka((int) $state) : '—'),
                TextColumn::make('stock')
                    ->formatStateUsing(fn ($state) => $state === null ? '∞' : (string) $state)
                    ->badge()
                    ->color(fn ($state) => $state === null ? 'gray' : ($state <= 0 ? 'danger' : ($state <= 5 ? 'warning' : 'success'))),
            ])
            ->headerActions([
                CreateAction::make()
                    ->after(function (ProductVariant $record): void {
                        if ($record->image && ($webp = ImageOptimizer::toWebp($record->image)) !== $record->image) {
                            $record->updateQuietly(['image' => $webp]);
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(function (ProductVariant $record): void {
                        if ($record->image && ($webp = ImageOptimizer::toWebp($record->image)) !== $record->image) {
                            $record->updateQuietly(['image' => $webp]);
                        }
                    }),
                DeleteAction::make(),
            ]);
    }
}
