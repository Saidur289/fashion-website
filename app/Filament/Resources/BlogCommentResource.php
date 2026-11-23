<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogCommentResource\Pages\ManageBlogComments;
use App\Models\BlogComment;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class BlogCommentResource extends Resource
{
    protected static ?string $model = BlogComment::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.inbox');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.blog_comments');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = BlogComment::where('is_approved', false)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string | array | null
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Toggle::make('is_approved')->label('Approved'),
            Textarea::make('reply')->label('Shop Reply')->rows(3)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('post.title')->label('Article')->limit(30)->weight('bold')->searchable(),
                TextColumn::make('name')->searchable()->description(fn (BlogComment $r) => $r->email),
                TextColumn::make('body')->limit(60)->wrap(),
                IconColumn::make('is_approved')->label('Approved')->boolean(),
                TextColumn::make('created_at')->date('d M Y')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_approved')->label('Approved'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (BlogComment $r) => ! $r->is_approved)
                    ->action(function (BlogComment $r): void {
                        $r->update(['is_approved' => true]);
                        Notification::make()->title('Comment approved')->success()->send();
                    }),
                Action::make('reply')
                    ->label('Reply')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('primary')
                    ->form([
                        Textarea::make('reply')->default(fn (BlogComment $r) => $r->reply)->rows(3)->required(),
                        Toggle::make('is_approved')->label('Approve comment too')->default(true),
                    ])
                    ->action(function (BlogComment $r, array $data): void {
                        $r->update(['reply' => $data['reply'], 'is_approved' => (bool) $data['is_approved']]);
                        Notification::make()->title('Reply saved')->success()->send();
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageBlogComments::route('/')];
    }
}
