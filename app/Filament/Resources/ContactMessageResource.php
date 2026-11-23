<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessageResource\Pages\ManageContactMessages;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-envelope';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.inbox');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.messages');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = ContactMessage::where('is_read', false)->count();

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
            TextInput::make('name')->disabled(),
            TextInput::make('phone')->disabled(),
            TextInput::make('email')->disabled(),
            TextInput::make('subject')->disabled(),
            Textarea::make('message')->disabled()->rows(5)->columnSpanFull(),
            Toggle::make('is_read')->label('Marked as Read'),
            Textarea::make('admin_note')->label('Internal Note')->rows(3)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('name')->searchable(['name', 'phone', 'email', 'subject'])->weight('bold')
                    ->description(fn (ContactMessage $r) => trim(($r->phone ?: '').' '.($r->email ?: ''))),
                TextColumn::make('subject')->placeholder('(No subject)')->limit(35),
                TextColumn::make('message')->limit(55)->wrap(),
                IconColumn::make('is_read')->label('Read')->boolean(),
                TextColumn::make('created_at')->label('Date')->dateTime('d M Y, h:i A')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_read')->label('Read Status'),
            ])
            ->recordActions([
                Action::make('toggleRead')
                    ->label(fn (ContactMessage $r) => $r->is_read ? 'Unread' : 'Mark Read')
                    ->icon('heroicon-o-check')
                    ->color(fn (ContactMessage $r) => $r->is_read ? 'gray' : 'success')
                    ->action(fn (ContactMessage $r) => $r->update(['is_read' => ! $r->is_read])),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageContactMessages::route('/')];
    }
}
