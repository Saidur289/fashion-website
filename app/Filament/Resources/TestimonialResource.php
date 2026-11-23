<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestimonialResource\Pages\ManageTestimonials;
use App\Models\Testimonial;
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

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-bottom-center-text';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.storefront');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.testimonials');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(120),
            TextInput::make('location')->maxLength(140)->placeholder('Dhaka — K-Beauty Customer'),
            Textarea::make('quote')->required()->rows(4)->columnSpanFull(),
            Select::make('rating')->options([5 => '5 Stars', 4 => '4 Stars', 3 => '3 Stars'])->default(5)->required(),
            TextInput::make('sort_order')->numeric()->default(0),
            FileUpload::make('photo')->disk('public_root')->directory('images/uploads/testimonials')->image()->columnSpanFull(),
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
                ImageColumn::make('photo')->label('')->disk('public_root')->size(40)->circular(),
                TextColumn::make('name')->weight('bold')->description(fn (Testimonial $r) => $r->location),
                TextColumn::make('quote')->limit(60)->wrap(),
                ToggleColumn::make('is_active')->label('Active'),
            ])
            ->recordActions([
                EditAction::make()->after(function (Testimonial $record): void {
                    if ($record->photo && ($webp = ImageOptimizer::toWebp($record->photo)) !== $record->photo) {
                        $record->updateQuietly(['photo' => $webp]);
                    }
                }),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageTestimonials::route('/')];
    }
}
