<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NewsletterSubscriberResource\Pages\ManageNewsletterSubscribers;
use App\Models\NewsletterSubscriber;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class NewsletterSubscriberResource extends Resource
{
    protected static ?string $model = NewsletterSubscriber::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.inbox');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.newsletter');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            Toggle::make('is_active')->label('Subscribed')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('email')->searchable()->copyable()->weight('bold'),
                IconColumn::make('is_active')->label('Active')->boolean(),
                TextColumn::make('subscribed_at')->label('Subscribed')->dateTime('d M Y, h:i A')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')->label('Active'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function streamCsv(): StreamedResponse
    {
        $subs = NewsletterSubscriber::query()->where('is_active', true)->orderByDesc('id')->get();

        return response()->streamDownload(function () use ($subs) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Email', 'Subscribed At']);
            foreach ($subs as $s) {
                fputcsv($out, [$s->email, ($s->subscribed_at ?? $s->created_at)?->format('Y-m-d H:i')]);
            }
            fclose($out);
        }, 'safebuy-newsletter-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public static function getPages(): array
    {
        return ['index' => ManageNewsletterSubscribers::route('/')];
    }
}
