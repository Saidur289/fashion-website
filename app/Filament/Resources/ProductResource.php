<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Services\ImageOptimizer;
use App\Support\ProductBlocks;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cube';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.catalog');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.products');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Product')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Basic & Pricing')
                        ->icon('heroicon-o-tag')
                        ->schema([
                            Section::make('Identity')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('name')
                                        ->required()
                                        ->maxLength(200)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(fn (?string $state, Set $set, ?Product $record) => $record ? null : $set('slug', Str::slug((string) $state))),
                                    TextInput::make('slug')
                                        ->required()
                                        ->maxLength(200)
                                        ->unique(ignoreRecord: true),
                                    Select::make('category_id')
                                        ->relationship('category', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    Select::make('brand_id')
                                        ->relationship('brand', 'name')
                                        ->searchable()
                                        ->preload(),
                                    TextInput::make('sku')->label('SKU')->maxLength(80),
                                    Select::make('tags')
                                        ->relationship('tags', 'name')
                                        ->multiple()
                                        ->searchable()
                                        ->preload(),
                                    Select::make('attributeValues')
                                        ->label('Attributes (Colors / Sizes for Filtering)')
                                        ->relationship('attributeValues', 'value')
                                        ->getOptionLabelFromRecordUsing(fn ($record) => ($record->attribute?->name ? $record->attribute->name.': ' : '').$record->value)
                                        ->multiple()
                                        ->searchable()
                                        ->preload()
                                        ->columnSpanFull(),
                                ]),

                            Section::make('Price, Stock & Flags')
                                ->columns(4)
                                ->schema([
                                    TextInput::make('price')->label('Price (Tk)')->numeric()->required()->minValue(0),
                                    TextInput::make('old_price')->label('Old Price (Tk)')->numeric()->minValue(0),
                                    TextInput::make('stock')->label('Stock (empty = unlimited)')->numeric()->minValue(0),
                                    DateTimePicker::make('sale_ends_at')->label('Offer Ends At'),
                                    Toggle::make('is_active')->label('Visible on Site')->default(true),
                                    Toggle::make('is_featured')->label('Featured'),
                                    Toggle::make('is_new_arrival')->label('New Arrival'),
                                    Toggle::make('is_trending')->label('Trending'),
                                    Toggle::make('is_best_seller')->label('Best Selling'),
                                    TextInput::make('sort_order')->numeric()->default(0),
                                ]),
                        ]),

                    Tab::make('Images (Auto WebP)')
                        ->icon('heroicon-o-photo')
                        ->schema([
                            Section::make('Card Images')
                                ->columns(2)
                                ->schema([
                                    FileUpload::make('image')
                                        ->label('Primary Image')
                                        ->disk('public_root')
                                        ->directory('images/uploads/products')
                                        ->image()
                                        ->required(),
                                    FileUpload::make('image_hover')
                                        ->label('Hover / Second Image')
                                        ->disk('public_root')
                                        ->directory('images/uploads/products')
                                        ->image(),
                                ]),

                            Section::make('Gallery Images (Drag to Reorder)')
                                ->schema([
                                    Repeater::make('images')
                                        ->relationship()
                                        ->orderColumn('sort_order')
                                        ->reorderable()
                                        ->grid(3)
                                        ->schema([
                                            FileUpload::make('path')
                                                ->label('Image')
                                                ->disk('public_root')
                                                ->directory('images/uploads/products')
                                                ->image()
                                                ->required(),
                                        ]),
                                ]),
                        ]),

                    Tab::make('Description & Details')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Section::make('Product Information')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('origin')->label('Country / Origin')->placeholder('South Korea / Japan'),
                                    TextInput::make('skin_type')->label('Skin Type')->placeholder('All skin types, Oily, Dry…'),
                                    TextInput::make('volume')->label('Volume / Net Weight')->placeholder('50ml / 100g'),
                                    Textarea::make('summary')
                                        ->label('Short Key Features (one per line)')
                                        ->rows(4)
                                        ->columnSpanFull(),
                                    Textarea::make('description')
                                        ->label('Description (use "## Heading", "- bullet", "1. step")')
                                        ->rows(7)
                                        ->formatStateUsing(fn ($state) => is_array($state) ? ProductBlocks::toText($state) : (string) $state)
                                        ->dehydrateStateUsing(fn ($state) => ProductBlocks::fromText($state))
                                        ->columnSpanFull(),
                                    Textarea::make('how_to_use')
                                        ->label('How to Use (use "## Heading", "1. step", "- tip")')
                                        ->rows(5)
                                        ->formatStateUsing(fn ($state) => is_array($state) ? ProductBlocks::toText($state) : (string) $state)
                                        ->dehydrateStateUsing(fn ($state) => ProductBlocks::fromText($state))
                                        ->columnSpanFull(),
                                    Textarea::make('ingredients')
                                        ->label('Ingredients')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                    KeyValue::make('specs')
                                        ->label('Additional Information / Specifications')
                                        ->keyLabel('Attribute (e.g. Size, Country)')
                                        ->valueLabel('Value (e.g. 50ml, Korea)')
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    Tab::make('Related & SEO')
                        ->icon('heroicon-o-sparkles')
                        ->schema([
                            Section::make('Related & Frequently Bought Together')
                                ->columns(2)
                                ->schema([
                                    Select::make('boughtTogether')
                                        ->label('Frequently Bought Together')
                                        ->relationship(
                                            'boughtTogether',
                                            'name',
                                            fn (Builder $q, ?Product $record) => $record ? $q->where('products.id', '!=', $record->id) : $q
                                        )
                                        ->pivotData(['type' => 'bought_together'])
                                        ->multiple()
                                        ->searchable()
                                        ->preload(),
                                    Select::make('related')
                                        ->label('Similar / Related Products')
                                        ->relationship(
                                            'related',
                                            'name',
                                            fn (Builder $q, ?Product $record) => $record ? $q->where('products.id', '!=', $record->id) : $q
                                        )
                                        ->pivotData(['type' => 'related'])
                                        ->multiple()
                                        ->searchable()
                                        ->preload(),
                                ]),
                            Section::make('SEO Meta')
                                ->schema([
                                    TextInput::make('meta_title')->maxLength(160),
                                    Textarea::make('meta_description')->rows(3)->maxLength(300),
                                ]),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->disk('public_root')
                    ->size(44)
                    ->square(),
                TextColumn::make('name')
                    ->searchable(['name', 'sku', 'slug'])
                    ->sortable()
                    ->weight('bold')
                    ->description(fn (Product $r) => trim(($r->sku ? 'SKU: '.$r->sku.' · ' : '').($r->brand?->name ?? ''), ' ·')),
                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('price')
                    ->sortable()
                    ->formatStateUsing(fn (Product $r) => $r->priceLabel())
                    ->description(fn (Product $r) => $r->old_price ? 'Was '.Product::taka($r->old_price) : null),
                TextColumn::make('stock')
                    ->label('Stock')
                    ->sortable()
                    ->formatStateUsing(function (Product $r) {
                        $s = $r->totalStock();

                        return $s === null ? '∞' : (string) $s;
                    })
                    ->badge()
                    ->color(function (Product $r) {
                        $s = $r->totalStock();
                        if ($s === null) {
                            return 'gray';
                        }

                        return $s <= 0 ? 'danger' : ($s <= 5 ? 'warning' : 'success');
                    }),
                TextColumn::make('variants_count')
                    ->counts('variants')
                    ->label('Variants')
                    ->badge()
                    ->color('info'),
                TextColumn::make('sold_count')->label('Sold')->sortable(),
                IconColumn::make('is_active')->label('Visible')->boolean(),
            ])
            ->filters([
                SelectFilter::make('category_id')->label('Category')->relationship('category', 'name'),
                SelectFilter::make('brand_id')->label('Brand')->relationship('brand', 'name'),
                TernaryFilter::make('is_active')->label('Visible on Site'),
                TernaryFilter::make('is_featured')->label('Featured'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('duplicate')
                    ->label('Copy')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->action(function (Product $record): void {
                        $copy = self::duplicateProduct($record);
                        Notification::make()->title("Copied to “{$copy->name}”")->success()->send();
                    }),
                DeleteAction::make()
                    ->after(fn (Product $record) => ActivityLog::record('deleted', "Moved product “{$record->name}” to Trash", $record)),
                RestoreAction::make()
                    ->after(fn (Product $record) => ActivityLog::record('restored', "Restored product “{$record->name}” from Trash", $record)),
                ForceDeleteAction::make()
                    ->after(fn (Product $record) => ActivityLog::record('deleted', "Permanently deleted product “{$record->name}”", $record)),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkHide')
                        ->label('Hide Selected')
                        ->icon('heroicon-o-eye-slash')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => false]);
                            Notification::make()->title($records->count().' products hidden')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('bulkShow')
                        ->label('Show Selected')
                        ->icon('heroicon-o-eye')
                        ->action(function (Collection $records): void {
                            $records->each->update(['is_active' => true]);
                            Notification::make()->title($records->count().' products made visible')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function duplicateProduct(Product $source): Product
    {
        $source->load(['tags', 'attributeValues', 'images', 'variants.values']);

        $baseSlug = $source->slug.'-copy';
        $slug = $baseSlug;
        $i = 2;
        while (Product::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$i++;
        }

        $copy = $source->replicate(['sold_count', 'reviews_count', 'rating']);
        $copy->name = $source->name.' (Copy)';
        $copy->slug = $slug;
        $copy->sku = $source->sku ? $source->sku.'-COPY' : null;
        $copy->is_active = false;
        $copy->sold_count = 0;
        $copy->reviews_count = 0;
        $copy->rating = null;
        $copy->save();

        $copy->tags()->sync($source->tags->pluck('id'));
        $copy->attributeValues()->sync($source->attributeValues->pluck('id'));

        foreach ($source->images as $img) {
            $copy->images()->create(['path' => $img->path, 'sort_order' => $img->sort_order]);
        }

        foreach ($source->variants as $v) {
            $vc = $copy->variants()->create($v->only(['sku', 'price', 'old_price', 'stock', 'image', 'sort_order']));
            $vc->values()->sync($v->values->pluck('id'));
        }

        ActivityLog::record('created', "Duplicated product “{$source->name}” into “{$copy->name}”", $copy);

        return $copy;
    }

    public static function optimizeProductImages(Product $product): void
    {
        $changed = [];
        if ($product->image && ($webp = ImageOptimizer::toWebp($product->image)) !== $product->image) {
            $changed['image'] = $webp;
        }
        if ($product->image_hover && ($webp = ImageOptimizer::toWebp($product->image_hover)) !== $product->image_hover) {
            $changed['image_hover'] = $webp;
        }
        if ($changed) {
            $product->updateQuietly($changed);
        }

        foreach ($product->images()->get() as $img) {
            if (($webp = ImageOptimizer::toWebp($img->path)) !== $img->path) {
                $img->updateQuietly(['path' => $webp]);
            }
        }
    }

    public static function streamCsv(): StreamedResponse
    {
        $products = Product::with(['category', 'brand'])->orderBy('id')->get();

        return response()->streamDownload(function () use ($products) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['name', 'slug', 'sku', 'category', 'brand', 'price', 'old_price', 'stock', 'origin', 'skin_type', 'volume', 'image', 'is_active', 'is_featured']);

            foreach ($products as $p) {
                fputcsv($out, [
                    $p->name,
                    $p->slug,
                    $p->sku,
                    $p->category?->name,
                    $p->brand?->name,
                    $p->price,
                    $p->old_price,
                    $p->stock,
                    $p->origin,
                    $p->skin_type,
                    $p->volume,
                    $p->image,
                    $p->is_active ? 1 : 0,
                    $p->is_featured ? 1 : 0,
                ]);
            }
            fclose($out);
        }, 'safebuy-products-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class])
            ->with(['category', 'brand', 'variants']);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\VariantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
