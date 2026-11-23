<?php

namespace App\Filament\Widgets;

use App\Models\ConciergeRequest;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $validStatuses = ['pending', 'confirmed', 'shipped', 'delivered'];

        $todayOrders = Order::whereDate('created_at', today());
        $todaySales = (int) (clone $todayOrders)->whereIn('status', $validStatuses)->sum('total');
        $todayCount = (clone $todayOrders)->count();

        $weekOrders = Order::where('created_at', '>=', now()->startOfWeek());
        $weekSales = (int) (clone $weekOrders)->whereIn('status', $validStatuses)->sum('total');
        $weekCount = (clone $weekOrders)->count();

        $monthOrders = Order::where('created_at', '>=', now()->startOfMonth());
        $monthSales = (int) (clone $monthOrders)->whereIn('status', $validStatuses)->sum('total');
        $monthCount = (clone $monthOrders)->count();

        $pendingCount = Order::where('status', 'pending')->count();
        $bkashCheckCount = Order::where('payment_status', 'pending_check')->count();

        $lowProducts = Product::whereNotNull('stock')->where('stock', '<=', 5)->count();
        $lowVariants = ProductVariant::whereNotNull('stock')->where('stock', '<=', 5)->count();
        $lowStockTotal = $lowProducts + $lowVariants;

        $pendingReviews = Review::where('is_approved', false)->count();
        $newConcierge = ConciergeRequest::where('status', 'new')->count();
        $unreadMessages = ContactMessage::where('is_read', false)->count();

        return [
            Stat::make("Today's Sales", Product::taka($todaySales))
                ->description("{$todayCount} orders today")
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make('This Week', Product::taka($weekSales))
                ->description("{$weekCount} orders this week")
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('primary'),

            Stat::make('This Month', Product::taka($monthSales))
                ->description("{$monthCount} orders this month")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Pending Orders', (string) $pendingCount)
                ->description($bkashCheckCount > 0 ? "{$bkashCheckCount} bKash awaiting check" : 'Orders waiting confirmation')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingCount > 0 ? 'warning' : 'success'),

            Stat::make('Low Stock Alerts', (string) $lowStockTotal)
                ->description("{$lowProducts} products, {$lowVariants} variants (≤ 5)")
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockTotal > 0 ? 'danger' : 'success'),

            Stat::make('Inbox & Reviews', (string) ($newConcierge + $unreadMessages + $pendingReviews))
                ->description("{$newConcierge} concierge · {$unreadMessages} messages · {$pendingReviews} reviews")
                ->descriptionIcon('heroicon-m-inbox')
                ->color(($newConcierge + $unreadMessages) > 0 ? 'warning' : 'gray'),
        ];
    }
}
