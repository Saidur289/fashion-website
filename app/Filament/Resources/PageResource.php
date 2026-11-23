<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages\ManagePages;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.pages');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required()->maxLength(160),
            TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(100)
                ->helperText('Built-in slugs: about, terms, privacy-policy, shipping-returns'),
            TextInput::make('subtitle')->maxLength(240)->columnSpanFull(),
            Textarea::make('body')
                ->label('Page Content (Markdown supported)')
                ->required()
                ->rows(12)
                ->columnSpanFull(),
            TextInput::make('meta_title')->maxLength(160),
            TextInput::make('meta_description')->maxLength(260),
            Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->weight('bold')->searchable(),
                TextColumn::make('slug')->badge()->color('primary'),
                IconColumn::make('is_active')->label('Active')->boolean(),
                TextColumn::make('updated_at')->label('Updated')->date('d M Y'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePages::route('/')];
    }
}
