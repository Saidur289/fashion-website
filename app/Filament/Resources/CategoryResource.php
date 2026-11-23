<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages\ManageCategories;
use App\Models\Category;
use App\Services\ImageOptimizer;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.categories');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(120)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (?string $state, Set $set, ?Category $record) => $record ? null : $set('slug', Str::slug((string) $state))),
            TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(120),
            Select::make('group')
                ->options([
                    Category::GROUP_BEAUTY => 'Beauty & Skincare',
                    Category::GROUP_GADGET => 'Smart Gadgets',
                ])
                ->default(Category::GROUP_BEAUTY)
                ->required(),
            TextInput::make('sort_order')->numeric()->default(0),
            FileUpload::make('image')
                ->disk('public_root')
                ->directory('images/uploads/categories')
                ->image()
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image')->label('')->disk('public_root')->size(40)->circular(),
                TextColumn::make('name')->searchable()->sortable()->weight('bold'),
                TextColumn::make('slug')->color('gray'),
                TextColumn::make('group')->badge()->color(fn (string $state) => $state === 'beauty' ? 'warning' : 'primary'),
                TextColumn::make('products_count')->counts('products')->label('Products')->badge(),
                TextColumn::make('sort_order')->label('Order')->sortable(),
            ])
            ->recordActions([
                EditAction::make()->after(function (Category $record): void {
                    if ($record->image && ($webp = ImageOptimizer::toWebp($record->image)) !== $record->image) {
                        $record->updateQuietly(['image' => $webp]);
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
            'index' => ManageCategories::route('/'),
        ];
    }
}
