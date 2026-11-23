<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages\ManageBanners;
use App\Models\Banner;
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

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.storefront');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.banners');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('slot')->options(Banner::SLOTS)->required(),
            Select::make('theme')->options(['light' => 'Light Theme', 'dark' => 'Dark Theme'])->default('light')->required(),
            TextInput::make('eyebrow')->maxLength(120),
            TextInput::make('title')->required()->maxLength(200),
            Textarea::make('subtitle')->rows(3)->columnSpanFull(),
            TextInput::make('cta_label')->label('Button Label')->maxLength(80),
            TextInput::make('cta_url')->label('Button Link')->maxLength(255),
            FileUpload::make('image')->disk('public_root')->directory('images/uploads/banners')->image()->columnSpanFull(),
            Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image')->label('')->disk('public_root')->size(44)->square(),
                TextColumn::make('slot')->badge()->formatStateUsing(fn (string $state) => Banner::SLOTS[$state] ?? $state)->color('primary'),
                TextColumn::make('title')->weight('bold')->limit(40),
                TextColumn::make('cta_label')->label('CTA')->placeholder('—'),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->recordActions([
                EditAction::make()->after(function (Banner $record): void {
                    if ($record->image && ($webp = ImageOptimizer::toWebp($record->image)) !== $record->image) {
                        $record->updateQuietly(['image' => $webp]);
                    }
                }),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageBanners::route('/')];
    }
}
