<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdminUserResource\Pages\ManageAdminUsers;
use App\Models\ActivityLog;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AdminUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'admin-users';

    protected static ?string $modelLabel = 'Admin User';

    protected static ?string $pluralModelLabel = 'Admin Users & Roles';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.admins');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isOwner() ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(120),
            TextInput::make('email')->email()->required()->unique(table: 'users', ignoreRecord: true)->maxLength(160),
            TextInput::make('phone')->maxLength(20)->unique(table: 'users', ignoreRecord: true),
            Select::make('role')
                ->options([
                    'owner' => 'Owner — Full Access (Settings, Users, Everything)',
                    'manager' => 'Manager — Orders, Products, Catalog, Reports',
                    'staff' => 'Staff — Orders Only (View & Update Status)',
                ])
                ->default('staff')
                ->required(),
            TextInput::make('password')
                ->password()
                ->revealable()
                ->required(fn (string $operation) => $operation === 'create')
                ->dehydrated(fn (?string $state) => filled($state))
                ->minLength(8)
                ->helperText('Leave blank while editing to keep the current password.'),
            Toggle::make('is_blocked')->label('Blocked (Disable login)'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id')
            ->columns([
                TextColumn::make('name')->searchable()->weight('bold'),
                TextColumn::make('email')->searchable(),
                TextColumn::make('phone')->placeholder('—'),
                TextColumn::make('role')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->color(fn (string $state) => match ($state) {
                        'owner' => 'warning',
                        'manager' => 'primary',
                        default => 'gray',
                    }),
                IconColumn::make('is_blocked')->label('Blocked')->boolean()->trueColor('danger')->falseColor('success'),
                TextColumn::make('created_at')->date('d M Y'),
            ])
            ->recordActions([
                EditAction::make()
                    ->using(function (User $record, array $data): User {
                        $role = $data['role'] ?? $record->role;
                        $blocked = (bool) ($data['is_blocked'] ?? false);
                        unset($data['role'], $data['is_blocked']);
                        $record->fill($data);
                        $record->forceFill(['role' => $role, 'is_blocked' => $blocked])->save();
                        ActivityLog::record('updated', "Updated admin user {$record->name} (role: {$role})", $record);

                        return $record;
                    }),
                DeleteAction::make()
                    ->after(fn (User $record) => ActivityLog::record('deleted', "Removed admin user {$record->name}", $record)),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereIn('role', ['owner', 'manager', 'staff']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAdminUsers::route('/'),
        ];
    }
}
