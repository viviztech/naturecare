<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Models\Order;
use Filament\Widgets\ChartWidget;

class MonthlyRevenueChart extends ChartWidget
{
    protected static ?int $sort = 30;

    protected ?string $heading = 'Revenue — Last 30 Days';

    protected function getData(): array
    {
        $start = now()->subDays(29)->startOfDay();

        $rows = Order::query()
            ->where('payment_status', PaymentStatus::Paid->value)
            ->where('created_at', '>=', $start)
            ->selectRaw('DATE(created_at) as day, SUM(total) as total_paise')
            ->groupBy('day')
            ->pluck('total_paise', 'day');

        $labels = [];
        $data = [];

        for ($date = $start->copy(); $date->lte(now()); $date->addDay()) {
            $key = $date->format('Y-m-d');
            $labels[] = $date->format('d M');
            $data[] = round(((int) ($rows[$key] ?? 0)) / 100, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Revenue (₹)',
                    'data' => $data,
                    'borderColor' => '#16a34a',
                    'backgroundColor' => 'rgba(22, 163, 74, 0.1)',
                    'fill' => true,
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
