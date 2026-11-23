<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConciergeRequestResource\Pages\ManageConciergeRequests;
use App\Models\ConciergeRequest;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class ConciergeRequestResource extends Resource
{
    protected static ?string $model = ConciergeRequest::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-sparkles';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.inbox');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.concierge');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = ConciergeRequest::where('status', 'new')->count();

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
            TextInput::make('product_name')->disabled()->columnSpanFull(),
            TextInput::make('brand')->disabled(),
            TextInput::make('budget')->disabled(),
            TextInput::make('reference_url')->disabled()->columnSpanFull(),
            Textarea::make('notes')->disabled()->rows(3)->columnSpanFull(),
            Select::make('status')->options(ConciergeRequest::STATUSES)->required(),
            TextInput::make('quoted_price')->label('Quoted Price (Tk)')->numeric()->minValue(0),
            Textarea::make('admin_note')->label('Admin Note')->rows(3)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                ImageColumn::make('image')->label('')->disk('public_root')->size(40)->square(),
                TextColumn::make('product_name')->searchable(['product_name', 'brand', 'name', 'phone'])->weight('bold')->description(fn (ConciergeRequest $r) => $r->brand ?: '—'),
                TextColumn::make('name')->label('Customer')->description(fn (ConciergeRequest $r) => $r->phone),
                TextColumn::make('budget')->placeholder('—'),
                TextColumn::make('quoted_price')->label('Quote')->formatStateUsing(fn ($state) => $state ? Product::taka((int) $state) : '—'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ConciergeRequest::STATUSES[$state] ?? ucfirst($state))
                    ->color(fn (string $state) => match ($state) {
                        'new' => 'warning',
                        'searching' => 'primary',
                        'quoted' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->label('Date')->dateTime('d M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(ConciergeRequest::STATUSES),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageConciergeRequests::route('/')];
    }
}
