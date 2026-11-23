<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamMemberResource\Pages\ManageTeamMembers;
use App\Models\TeamMember;
use App\Services\ImageOptimizer;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class TeamMemberResource extends Resource
{
    protected static ?string $model = TeamMember::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.content');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.team');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(120),
            TextInput::make('role')->required()->maxLength(120),
            Textarea::make('bio')->rows(3)->columnSpanFull(),
            FileUpload::make('photo')->disk('public_root')->directory('images/uploads/team')->image()->columnSpanFull(),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->label('Active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('photo')->label('')->disk('public_root')->size(40)->circular(),
                TextColumn::make('name')->weight('bold')->searchable(),
                TextColumn::make('role')->color('primary'),
                TextColumn::make('sort_order')->label('Order')->sortable(),
                IconColumn::make('is_active')->label('Active')->boolean(),
            ])
            ->recordActions([
                EditAction::make()->after(function (TeamMember $record): void {
                    if ($record->photo && ($webp = ImageOptimizer::toWebp($record->photo)) !== $record->photo) {
                        $record->updateQuietly(['photo' => $webp]);
                    }
                }),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageTeamMembers::route('/')];
    }
}
