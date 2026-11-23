<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Services\Settings;
use Filament\Widgets\ChartWidget;

class SalesChartWidget extends ChartWidget
{
    protected static ?int $sort = 2;

    protected ?string $heading = 'Sales — Last 30 Days (Taka)';

    protected int | string | array $columnSpan = 'full';

    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $start = now()->subDays(29)->startOfDay();
        $rows = Order::query()
            ->where('created_at', '>=', $start)
            ->whereNotIn('status', ['cancelled', 'returned'])
            ->selectRaw('DATE(created_at) as day, SUM(total) as revenue, COUNT(*) as orders_count')
            ->groupBy('day')
            ->pluck('revenue', 'day');

        $labels = [];
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('d M');
            $data[] = (int) ($rows[$key] ?? 0);
        }

        $colors = Settings::colors();

        return [
            'datasets' => [
                [
                    'label' => 'Sales (Tk)',
                    'data' => $data,
                    'borderColor' => $colors['blue'],
                    'backgroundColor' => $colors['blue_light'],
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
