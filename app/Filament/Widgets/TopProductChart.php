<?php

namespace App\Filament\Widgets;

use App\Models\OrderItemModel;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class TopProductChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Top 5 Buku Terlaris';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $period = $this->filters['period'] ?? 'today';

        $baseQuery = OrderItemModel::query()
            ->with('book:id,title')
            ->selectRaw('order_items.book_id, SUM(order_items.qty) as total_terjual')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'confirmed')
            ->whereNotNull('order_items.book_id');

        $query = match ($period) {

            'today' => (clone $baseQuery)
                ->whereDate('orders.created_at', now()->toDateString()),

            'yesterday' => (clone $baseQuery)
                ->whereDate('orders.created_at', now()->subDay()->toDateString()),

            'this_month' => (clone $baseQuery)
                ->whereBetween('orders.created_at', [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ]),

            'last_month' => (clone $baseQuery)
                ->whereBetween('orders.created_at', [
                    now()->subMonthNoOverflow()->startOfMonth(),
                    now()->subMonthNoOverflow()->endOfMonth(),
                ]),

            'this_year' => (clone $baseQuery)
                ->whereBetween('orders.created_at', [
                    now()->startOfYear(),
                    now()->endOfYear(),
                ]),

            'last_year' => (clone $baseQuery)
                ->whereBetween('orders.created_at', [
                    now()->subYearNoOverflow()->startOfYear(),
                    now()->subYearNoOverflow()->endOfYear(),
                ]),

            default => $baseQuery,
        };

        $data = $query->groupBy('order_items.book_id')
            ->orderByDesc('total_terjual')
            ->limit(5)
            ->get();

        return [
            'datasets' => [[
                'label' => 'Jumlah Terjual',
                'data'  => $data->pluck('total_terjual')->map(fn($v) => (int) $v)->toArray(),
            ]],
            'labels' => $data->map(fn($item) => $item->book?->title ?? 'Unknown')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
