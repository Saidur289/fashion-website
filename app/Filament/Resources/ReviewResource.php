<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages\ManageReviews;
use App\Models\ActivityLog;
use App\Models\Review;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
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
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-star';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.customers');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.reviews');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Review::where('is_approved', false)->count();

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
            Toggle::make('is_approved')->label('Approved (Visible on product page)'),
            Textarea::make('reply')->label('Shop Reply')->rows(3)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('product.name')->searchable()->limit(28)->weight('bold'),
                TextColumn::make('user.name')->label('Customer')->searchable(['name', 'phone']),
                TextColumn::make('rating')
                    ->badge()
                    ->formatStateUsing(fn ($state) => str_repeat('★', (int) $state).' ('.$state.')')
                    ->color(fn ($state) => $state >= 4 ? 'success' : ($state >= 3 ? 'warning' : 'danger')),
                TextColumn::make('body')->limit(60)->wrap(),
                TextColumn::make('reply')->label('Shop Reply')->limit(40)->placeholder('—'),
                IconColumn::make('is_approved')->label('Approved')->boolean(),
                TextColumn::make('created_at')->date('d M Y')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_approved')->label('Approval Status'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Review $record) => ! $record->is_approved)
                    ->action(function (Review $record): void {
                        $record->update(['is_approved' => true]);
                        $record->product?->refreshRating();
                        ActivityLog::record('updated', "Approved review #{$record->id} on “{$record->product?->name}”", $record);
                        Notification::make()->title('Review approved')->success()->send();
                    }),
                Action::make('hide')
                    ->label('Hide')
                    ->icon('heroicon-o-eye-slash')
                    ->color('warning')
                    ->visible(fn (Review $record) => $record->is_approved)
                    ->action(function (Review $record): void {
                        $record->update(['is_approved' => false]);
                        $record->product?->refreshRating();
                        ActivityLog::record('updated', "Hidden review #{$record->id} on “{$record->product?->name}”", $record);
                        Notification::make()->title('Review hidden')->success()->send();
                    }),
                Action::make('reply')
                    ->label('Reply')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('primary')
                    ->form([
                        Textarea::make('reply')
                            ->label('Shop Reply')
                            ->default(fn (Review $record) => $record->reply)
                            ->rows(3)
                            ->required(),
                        Toggle::make('is_approved')
                            ->label('Approve review too')
                            ->default(fn (Review $record) => $record->is_approved ?: true),
                    ])
                    ->action(function (Review $record, array $data): void {
                        $record->update([
                            'reply' => $data['reply'],
                            'is_approved' => (bool) $data['is_approved'],
                        ]);
                        $record->product?->refreshRating();
                        ActivityLog::record('updated', "Replied to review #{$record->id} on “{$record->product?->name}”", $record);
                        Notification::make()->title('Reply saved')->success()->send();
                    }),
                DeleteAction::make()
                    ->after(function (Review $record): void {
                        $record->product?->refreshRating();
                        ActivityLog::record('deleted', "Deleted review #{$record->id}", $record);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkApprove')
                        ->label('Approve Selected')
                        ->icon('heroicon-o-check-circle')
                        ->action(function (Collection $records): void {
                            foreach ($records as $r) {
                                $r->update(['is_approved' => true]);
                                $r->product?->refreshRating();
                            }
                            Notification::make()->title($records->count().' reviews approved')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make()
                        ->after(fn (Collection $records) => $records->each(fn (Review $r) => $r->product?->refreshRating())),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageReviews::route('/'),
        ];
    }
}
