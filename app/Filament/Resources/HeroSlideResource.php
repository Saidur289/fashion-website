<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroSlideResource\Pages\ManageHeroSlides;
use App\Models\HeroSlide;
use App\Services\ImageOptimizer;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class HeroSlideResource extends Resource
{
    protected static ?string $model = HeroSlide::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-photo';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.storefront');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.hero_slides');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('eyebrow')->maxLength(120)->placeholder('100% Authentic K-Beauty'),
            Select::make('layout_style')->options(HeroSlide::STYLES)->default('arch')->required(),
            Textarea::make('title')->required()->rows(2)->columnSpanFull()->helperText('Use a new line for a line break in the headline.'),
            Textarea::make('description')->rows(3)->columnSpanFull(),
            TextInput::make('cta_label')->label('Button Label')->default('Shop now')->maxLength(60),
            TextInput::make('cta_url')->label('Button Link')->default('/shop')->maxLength(255),
            TextInput::make('accent_color')->label('Slide Background Color')->placeholder('#F5E9D9 or #ECECEC'),
            TextInput::make('sort_order')->numeric()->default(0),
            FileUpload::make('bg_image')->label('Background Image (Optional)')->disk('public_root')->directory('images/uploads/hero')->image(),
            FileUpload::make('image_primary')->label('Primary Visual (Left Model / Right Product)')->disk('public_root')->directory('images/uploads/hero')->image(),
            FileUpload::make('image_secondary')->label('Secondary Visual (Right Products / Overlay Leaves)')->disk('public_root')->directory('images/uploads/hero')->image(),
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
                ImageColumn::make('image_primary')->label('')->disk('public_root')->size(46)->square(),
                TextColumn::make('title')->weight('bold')->limit(45)->description(fn (HeroSlide $r) => $r->eyebrow),
                TextColumn::make('layout_style')->badge()->color('primary'),
                TextColumn::make('cta_url')->label('Link')->color('gray'),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->recordActions([
                EditAction::make()->after(fn (HeroSlide $record) => self::optimizeImages($record)),
                DeleteAction::make(),
            ]);
    }

    public static function optimizeImages(HeroSlide $slide): void
    {
        $updates = [];
        foreach (['bg_image', 'image_primary', 'image_secondary'] as $col) {
            if ($slide->{$col} && ($webp = ImageOptimizer::toWebp($slide->{$col})) !== $slide->{$col}) {
                $updates[$col] = $webp;
            }
        }
        if ($updates) {
            $slide->updateQuietly($updates);
        }
    }

    public static function getPages(): array
    {
        return ['index' => ManageHeroSlides::route('/')];
    }
}
