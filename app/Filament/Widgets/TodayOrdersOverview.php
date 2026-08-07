<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Support\Money;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TodayOrdersOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $today = now()->startOfDay();

        $todaysOrders = Order::query()->where('created_at', '>=', $today);
        $todaysOrderCount = (clone $todaysOrders)->count();

        $todaysRevenuePaise = (clone $todaysOrders)
            ->where('payment_status', PaymentStatus::Paid->value)
            ->sum('total');

        $pendingCod = Order::query()
            ->where('payment_method', PaymentMethod::Cod->value)
            ->where('payment_status', PaymentStatus::Pending->value)
            ->where('order_status', '!=', OrderStatus::Cancelled->value)
            ->count();

        $lowStockThreshold = (int) Setting::get('low_stock_threshold', 10);
        $lowStockCount = ProductVariant::query()
            ->where('is_active', true)
            ->where('stock_qty', '<=', $lowStockThreshold)
            ->count();

        return [
            Stat::make("Today's Orders", (string) $todaysOrderCount)
                ->color('info'),
            Stat::make("Today's Revenue", Money::fromPaise((int) $todaysRevenuePaise)->format())
                ->description('Paid orders only')
                ->color('success'),
            Stat::make('Pending COD Orders', (string) $pendingCod)
                ->color($pendingCod > 0 ? 'warning' : 'success'),
            Stat::make('Low Stock Variants', (string) $lowStockCount)
                ->description("At or below {$lowStockThreshold} units")
                ->color($lowStockCount > 0 ? 'danger' : 'success'),
        ];
    }
}
