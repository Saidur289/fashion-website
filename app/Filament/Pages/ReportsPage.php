<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class ReportsPage extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.reports-page';

    public string $from;

    public string $until;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('admin.groups.sales');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.nav.reports');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->canManageCatalog() ?? false;
    }

    public function mount(): void
    {
        $this->from = now()->subDays(29)->format('Y-m-d');
        $this->until = now()->format('Y-m-d');
    }

    public function setRange(string $preset): void
    {
        $this->until = now()->format('Y-m-d');
        $this->from = match ($preset) {
            'today' => now()->format('Y-m-d'),
            '7d' => now()->subDays(6)->format('Y-m-d'),
            '30d' => now()->subDays(29)->format('Y-m-d'),
            'month' => now()->startOfMonth()->format('Y-m-d'),
            'year' => now()->startOfYear()->format('Y-m-d'),
            default => now()->subDays(29)->format('Y-m-d'),
        };
    }

    public function getSummaryProperty(): array
    {
        $base = Order::query()
            ->whereDate('created_at', '>=', $this->from)
            ->whereDate('created_at', '<=', $this->until);

        $totalOrders = (clone $base)->count();
        $activeOrders = (clone $base)->whereNotIn('status', ['cancelled', 'returned']);
        $grossSales = (int) (clone $activeOrders)->sum('total');
        $activeCount = (clone $activeOrders)->count();
        $deliveredSales = (int) (clone $base)->where('status', 'delivered')->sum('total');
        $cancelledCount = (clone $base)->where('status', 'cancelled')->count();
        $returnedCount = (clone $base)->where('status', 'returned')->count();

        return [
            'total_orders' => $totalOrders,
            'active_orders' => $activeCount,
            'gross_sales' => $grossSales,
            'delivered_sales' => $deliveredSales,
            'avg_order' => $activeCount > 0 ? (int) round($grossSales / $activeCount) : 0,
            'cancelled_count' => $cancelledCount,
            'returned_count' => $returnedCount,
            'cancel_rate' => $totalOrders > 0 ? round(($cancelledCount / $totalOrders) * 100, 1) : 0.0,
            'return_rate' => $totalOrders > 0 ? round(($returnedCount / $totalOrders) * 100, 1) : 0.0,
        ];
    }

    public function getByProductProperty(): array
    {
        return OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereDate('orders.created_at', '>=', $this->from)
            ->whereDate('orders.created_at', '<=', $this->until)
            ->whereNotIn('orders.status', ['cancelled', 'returned'])
            ->selectRaw('order_items.name, SUM(order_items.qty) as qty_sold, SUM(order_items.total) as revenue')
            ->groupBy('order_items.name')
            ->orderByDesc('revenue')
            ->limit(25)
            ->get()
            ->toArray();
    }

    public function getByCategoryProperty(): array
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->whereDate('orders.created_at', '>=', $this->from)
            ->whereDate('orders.created_at', '<=', $this->until)
            ->whereNotIn('orders.status', ['cancelled', 'returned'])
            ->selectRaw("COALESCE(categories.name, 'Uncategorized') as category, SUM(order_items.qty) as qty_sold, SUM(order_items.total) as revenue")
            ->groupBy('category')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($r) => (array) $r)
            ->all();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export Report CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->action(fn () => $this->downloadCsv()),
        ];
    }

    public function downloadCsv(): StreamedResponse
    {
        $products = $this->byProduct;
        $from = $this->from;
        $until = $this->until;

        return response()->streamDownload(function () use ($products, $from, $until) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ["SafeBuy Sales Report ({$from} to {$until})"]);
            fputcsv($out, ['Product', 'Qty Sold', 'Revenue (Tk)']);
            foreach ($products as $p) {
                fputcsv($out, [$p['name'], $p['qty_sold'], $p['revenue']]);
            }
            fclose($out);
        }, "safebuy-report-{$from}-to-{$until}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
