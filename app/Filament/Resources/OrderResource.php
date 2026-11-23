<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Cart;
use App\Services\OrderManager;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'number';

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.sales');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.orders');
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Order::where('status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string | array | null
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        $districts = array_combine(config('districts'), config('districts'));

        return $schema->components([
            Section::make('Customer & Delivery')
                ->columns(2)
                ->schema([
                    Select::make('user_id')
                        ->label('Existing Customer (Optional)')
                        ->relationship('user', 'name')
                        ->searchable(['name', 'phone', 'email'])
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function (?int $state, Set $set) {
                            if ($state && ($u = User::find($state))) {
                                $set('name', $u->name);
                                $set('phone', $u->phone);
                                $set('email', $u->email);
                                if ($addr = $u->addresses()->first()) {
                                    $set('district', $addr->district);
                                    $set('area', $addr->area);
                                    $set('address', $addr->address);
                                }
                            }
                        }),
                    TextInput::make('name')->required()->maxLength(120),
                    TextInput::make('phone')->required()->maxLength(20)->placeholder('01XXXXXXXXX'),
                    TextInput::make('email')->email()->maxLength(160),
                    Select::make('district')
                        ->options($districts)
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (?string $state, Set $set) {
                            if (! $state) {
                                return;
                            }
                            $method = Cart::methodForDistrict($state);
                            $set('shipping_method', $method);
                            $set('shipping', $method === 'inside_dhaka'
                                ? (int) config('safebuy.shipping.inside_dhaka', 70)
                                : (int) config('safebuy.shipping.outside_dhaka', 130));
                        }),
                    TextInput::make('area')->maxLength(120),
                    Textarea::make('address')->required()->rows(2)->columnSpanFull(),
                    Textarea::make('note')->label('Order Note')->rows(2)->columnSpanFull(),
                ]),

            Section::make('Order Items (Phone Order)')
                ->visibleOn('create')
                ->schema([
                    Repeater::make('items')
                        ->label('Products')
                        ->required()
                        ->minItems(1)
                        ->columns(4)
                        ->schema([
                            Select::make('product_id')
                                ->label('Product')
                                ->options(fn () => Product::query()->orderBy('name')->pluck('name', 'id'))
                                ->searchable()
                                ->required()
                                ->live()
                                ->afterStateUpdated(function (?int $state, Set $set) {
                                    $set('product_variant_id', null);
                                    if ($state && ($p = Product::find($state))) {
                                        $set('price', $p->price);
                                    }
                                }),
                            Select::make('product_variant_id')
                                ->label('Variant')
                                ->options(function (Get $get) {
                                    $pid = $get('product_id');
                                    if (! $pid) {
                                        return [];
                                    }

                                    return ProductVariant::where('product_id', $pid)
                                        ->with('values')
                                        ->get()
                                        ->mapWithKeys(fn (ProductVariant $v) => [$v->id => ($v->label() ?: 'Variant #'.$v->id).' — '.Product::taka($v->price)]);
                                })
                                ->live()
                                ->afterStateUpdated(function (?int $state, Set $set) {
                                    if ($state && ($v = ProductVariant::find($state))) {
                                        $set('price', $v->price);
                                    }
                                }),
                            TextInput::make('price')->label('Unit Price (Tk)')->numeric()->required()->minValue(0),
                            TextInput::make('qty')->label('Qty')->numeric()->default(1)->required()->minValue(1),
                        ]),
                ]),

            Section::make('Payment, Shipping & Status')
                ->columns(3)
                ->schema([
                    Select::make('status')
                        ->options(Order::STATUSES)
                        ->default('confirmed')
                        ->required(),
                    Select::make('payment_method')
                        ->options(Order::PAYMENTS)
                        ->default('cod')
                        ->required()
                        ->live(),
                    Select::make('payment_status')
                        ->options(Order::PAYMENT_STATUSES)
                        ->default('unpaid')
                        ->required(),
                    TextInput::make('bkash_sender')
                        ->label('bKash Sender No.')
                        ->visible(fn (Get $get) => $get('payment_method') === 'bkash')
                        ->maxLength(20),
                    TextInput::make('bkash_trx_id')
                        ->label('bKash TrxID')
                        ->visible(fn (Get $get) => $get('payment_method') === 'bkash')
                        ->maxLength(60),
                    Select::make('shipping_method')
                        ->options(Order::SHIPPING)
                        ->default('inside_dhaka')
                        ->required(),
                    TextInput::make('shipping')->label('Delivery Charge (Tk)')->numeric()->default(70)->required(),
                    TextInput::make('coupon_code')->label('Coupon Code')->maxLength(40),
                    TextInput::make('discount')->label('Discount (Tk)')->numeric()->default(0),
                    TextInput::make('tracking_code')->label('Courier Tracking No.')->maxLength(100),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),
                TextColumn::make('name')
                    ->label('Customer')
                    ->searchable(['name', 'phone', 'email'])
                    ->description(fn (Order $r) => $r->phone.' · '.$r->district),
                TextColumn::make('total')
                    ->label('Total')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => Product::taka((int) $state))
                    ->weight('bold'),
                TextColumn::make('payment_method')
                    ->label('Payment')
                    ->formatStateUsing(fn (Order $r) => $r->paymentLabel())
                    ->description(fn (Order $r) => $r->bkash_trx_id ? 'Trx: '.$r->bkash_trx_id : null)
                    ->searchable(['bkash_trx_id', 'bkash_sender']),
                TextColumn::make('payment_status')
                    ->label('Paid?')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Order::PAYMENT_STATUSES[$state] ?? ucfirst($state))
                    ->color(fn (string $state) => match ($state) {
                        'paid' => 'success',
                        'pending_check' => 'warning',
                        'refunded' => 'gray',
                        default => 'danger',
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Order::STATUSES[$state] ?? ucfirst($state))
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'info',
                        'shipped' => 'primary',
                        'delivered' => 'success',
                        'cancelled', 'returned' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('tracking_code')
                    ->label('Tracking')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(Order::STATUSES),
                SelectFilter::make('payment_method')->options(Order::PAYMENTS),
                SelectFilter::make('payment_status')->options(Order::PAYMENT_STATUSES),
                SelectFilter::make('district')
                    ->options(array_combine(config('districts'), config('districts')))
                    ->searchable(),
                Filter::make('created_at')
                    ->form([
                        Grid::make(2)->schema([
                            DatePicker::make('from')->label('From Date'),
                            DatePicker::make('until')->label('Until Date'),
                        ]),
                    ])
                    ->query(fn (Builder $q, array $data) => $q
                        ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))),
            ])
            ->recordActions([
                Action::make('updateStatus')
                    ->label('Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('primary')
                    ->form([
                        Select::make('status')
                            ->options(Order::STATUSES)
                            ->default(fn (Order $record) => $record->status)
                            ->required(),
                        TextInput::make('tracking_code')
                            ->label('Courier Tracking No.')
                            ->default(fn (Order $record) => $record->tracking_code),
                        Textarea::make('note')
                            ->label('Internal Note / Reason')
                            ->rows(2),
                        Toggle::make('send_sms')
                            ->label('Send SMS notification to customer')
                            ->default(true),
                    ])
                    ->action(function (Order $record, array $data): void {
                        $mgr = app(OrderManager::class);
                        if (($data['tracking_code'] ?? null) !== $record->tracking_code) {
                            $mgr->updateTracking($record, $data['tracking_code'] ?? null);
                        }
                        $mgr->transitionStatus($record, $data['status'], $data['note'] ?? null, (bool) ($data['send_sms'] ?? true));
                        Notification::make()->title("Order {$record->number} updated to ".Order::STATUSES[$data['status']])->success()->send();
                    }),

                Action::make('markPaid')
                    ->label('Mark Paid')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (Order $record) => $record->payment_status !== 'paid')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Order $record) => "Verify payment for {$record->number}?")
                    ->modalDescription(fn (Order $record) => $record->payment_method === 'bkash'
                        ? "bKash Sender: {$record->bkash_sender} | TrxID: {$record->bkash_trx_id} | Amount: ".Product::taka($record->total)
                        : 'Mark COD payment of '.Product::taka($record->total).' as received?')
                    ->action(function (Order $record): void {
                        app(OrderManager::class)->updatePaymentStatus($record, 'paid', 'Payment verified by admin.');
                        Notification::make()->title("Order {$record->number} marked as Paid")->success()->send();
                    }),

                Action::make('invoice')
                    ->label('Invoice')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (Order $record) => route('admin.orders.invoice', $record))
                    ->openUrlInNewTab(),

                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulkStatus')
                        ->label('Change Status')
                        ->icon('heroicon-o-arrow-path')
                        ->form([
                            Select::make('status')->options(Order::STATUSES)->required(),
                            Textarea::make('note')->label('Note')->rows(2),
                            Toggle::make('send_sms')->label('Send SMS to customers')->default(true),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $mgr = app(OrderManager::class);
                            foreach ($records as $order) {
                                $mgr->transitionStatus($order, $data['status'], $data['note'] ?? null, (bool) ($data['send_sms'] ?? true));
                            }
                            Notification::make()->title($records->count().' orders updated')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('exportSelected')
                        ->label('Export CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(fn (Collection $records) => self::streamCsv($records)),

                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function streamCsv(iterable $orders): StreamedResponse
    {
        return response()->streamDownload(function () use ($orders) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel Bangla support
            fputcsv($out, ['Order #', 'Date', 'Customer', 'Phone', 'District', 'Address', 'Subtotal', 'Discount', 'Shipping', 'Total', 'Payment', 'Payment Status', 'bKash TrxID', 'Status', 'Tracking']);

            foreach ($orders as $o) {
                fputcsv($out, [
                    $o->number,
                    $o->created_at?->format('Y-m-d H:i'),
                    $o->name,
                    $o->phone,
                    $o->district,
                    $o->address,
                    $o->subtotal,
                    $o->discount,
                    $o->shipping,
                    $o->total,
                    $o->payment_method,
                    $o->payment_status,
                    $o->bkash_trx_id,
                    $o->status,
                    $o->tracking_code,
                ]);
            }
            fclose($out);
        }, 'safebuy-orders-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemsRelationManager::class,
            RelationManagers\HistoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
