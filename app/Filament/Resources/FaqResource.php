<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FaqResource\Pages\ManageFaqs;
use App\Models\Faq;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.faqs');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('category')->options(Faq::CATEGORIES)->default('orders')->required(),
            TextInput::make('sort_order')->numeric()->default(0),
            TextInput::make('question')->required()->maxLength(255)->columnSpanFull(),
            Textarea::make('answer')->required()->rows(5)->columnSpanFull(),
            Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('category')->badge()->formatStateUsing(fn (string $state) => Faq::CATEGORIES[$state] ?? ucfirst($state)),
                TextColumn::make('question')->searchable()->wrap()->weight('bold'),
                TextColumn::make('sort_order')->label('Order')->sortable(),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->filters([
                SelectFilter::make('category')->options(Faq::CATEGORIES),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageFaqs::route('/')];
    }
}
