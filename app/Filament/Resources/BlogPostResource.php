<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogPost;
use App\Services\ImageOptimizer;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-newspaper';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.blog_posts');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Article')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(200)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (?string $state, Set $set, ?BlogPost $record) => $record ? null : $set('slug', Str::slug((string) $state))),
                    TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(200),
                    Select::make('blog_category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name')->required()->maxLength(80),
                            TextInput::make('slug')->required()->unique('blog_categories', 'slug')->maxLength(80),
                        ]),
                    Select::make('tags')
                        ->relationship('tags', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->createOptionForm([
                            TextInput::make('name')->required()->maxLength(60),
                            TextInput::make('slug')->required()->unique('blog_tags', 'slug')->maxLength(60),
                        ]),
                    FileUpload::make('image')
                        ->label('Cover Image (Auto WebP)')
                        ->disk('public_root')
                        ->directory('images/uploads/blog')
                        ->image()
                        ->columnSpanFull(),
                    Textarea::make('excerpt')->rows(2)->columnSpanFull(),
                    Textarea::make('body')
                        ->label('Article Content (Markdown supported: ## Heading, **bold**, - list)')
                        ->required()
                        ->rows(12)
                        ->columnSpanFull(),
                ]),

            Section::make('Attached Products & Publishing')
                ->columns(2)
                ->schema([
                    Select::make('products')
                        ->label('Featured Products in Article')
                        ->relationship('products', 'name')
                        ->multiple()
                        ->searchable()
                        ->preload()
                        ->columnSpanFull(),
                    Toggle::make('is_published')->label('Published')->default(true),
                    DateTimePicker::make('published_at')->label('Publish Date')->default(now()),
                    TextInput::make('meta_title')->maxLength(160),
                    TextInput::make('meta_description')->maxLength(260),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                ImageColumn::make('image')->label('')->disk('public_root')->size(44)->square(),
                TextColumn::make('title')->searchable()->sortable()->weight('bold')->limit(45),
                TextColumn::make('category.name')->badge()->color('primary')->placeholder('—'),
                TextColumn::make('views')->sortable(),
                IconColumn::make('is_published')->label('Live')->boolean(),
                TextColumn::make('published_at')->label('Date')->date('d M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('blog_category_id')->label('Category')->relationship('category', 'name'),
                TernaryFilter::make('is_published')->label('Published'),
            ])
            ->recordActions([
                Action::make('preview')
                    ->label('View')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (BlogPost $record) => route('blog.show', $record))
                    ->openUrlInNewTab()
                    ->visible(fn (BlogPost $record) => $record->is_published),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function optimizeImage(BlogPost $post): void
    {
        if ($post->image && ($webp = ImageOptimizer::toWebp($post->image)) !== $post->image) {
            $post->updateQuietly(['image' => $webp]);
        }
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }
}
