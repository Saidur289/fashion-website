<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CustomerResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'customers';

    protected static ?string $modelLabel = 'Customer';

    protected static ?string $pluralModelLabel = 'Customers';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.customers');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.customers');
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
            Toggle::make('is_blocked')->label('Blocked (cannot log in)'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('name')->searchable(['name', 'phone', 'email'])->weight('bold'),
                TextColumn::make('phone')->searchable()->copyable(),
                TextColumn::make('email')->placeholder('—')->toggleable(),
                TextColumn::make('orders_count')->counts('orders')->label('Orders')->badge()->color('primary')->sortable(),
                TextColumn::make('orders_sum_total')
                    ->sum('orders', 'total')
                    ->label('Total Spent')
                    ->formatStateUsing(fn ($state) => Product::taka((int) ($state ?? 0)))
                    ->sortable(),
                IconColumn::make('is_blocked')->label('Blocked')->boolean()->trueColor('danger')->falseColor('success'),
                TextColumn::make('created_at')->label('Joined')->date('d M Y')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_blocked')->label('Blocked'),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('toggleBlock')
                    ->label(fn (User $record) => $record->is_blocked ? 'Unblock' : 'Block')
                    ->icon(fn (User $record) => $record->is_blocked ? 'heroicon-o-lock-open' : 'heroicon-o-no-symbol')
                    ->color(fn (User $record) => $record->is_blocked ? 'success' : 'danger')
                    ->requiresConfirmation()
                    ->modalHeading(fn (User $record) => ($record->is_blocked ? 'Unblock ' : 'Block ').$record->name.'?')
                    ->action(function (User $record): void {
                        $next = ! $record->is_blocked;
                        $record->forceFill(['is_blocked' => $next])->save();
                        ActivityLog::record('updated', ($next ? 'Blocked' : 'Unblocked')." customer {$record->name} ({$record->phone})", $record);
                        Notification::make()->title($next ? 'Customer blocked' : 'Customer unblocked')->success()->send();
                    }),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('role', 'customer');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrdersRelationManager::class,
            RelationManagers\AddressesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'view' => Pages\ViewCustomer::route('/{record}'),
        ];
    }
}
