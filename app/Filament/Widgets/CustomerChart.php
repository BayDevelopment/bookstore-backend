<?php

namespace App\Filament\Widgets;

use App\Models\OrderModel;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class CustomerChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Top 5 Customer (Total Belanja)';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $period = $this->filters['period'] ?? 'this_month';

        $baseQuery = OrderModel::query()
            ->with('user:id,name')
            ->selectRaw('user_id, SUM(total) as total')
            ->whereNotNull('user_id');

        $query = match ($period) {

            'today' => (clone $baseQuery)
                ->whereDate('created_at', now()->toDateString()),

            'yesterday' => (clone $baseQuery)
                ->whereDate('created_at', now()->subDay()->toDateString()),

            'this_month' => (clone $baseQuery)
                ->whereBetween('created_at', [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ]),

            'last_month' => (clone $baseQuery)
                ->whereBetween('created_at', [
                    now()->subMonthNoOverflow()->startOfMonth(),
                    now()->subMonthNoOverflow()->endOfMonth(),
                ]),

            'this_year' => (clone $baseQuery)
                ->whereBetween('created_at', [
                    now()->startOfYear(),
                    now()->endOfYear(),
                ]),

            'last_year' => (clone $baseQuery)
                ->whereBetween('created_at', [
                    now()->subYearNoOverflow()->startOfYear(),
                    now()->subYearNoOverflow()->endOfYear(),
                ]),

            default => $baseQuery,
        };

        $data = $query->groupBy('user_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Belanja',
                    'data' => $data->pluck('total')->map(fn($v) => (float) $v)->toArray(),
                ],
            ],
            'labels' => $data->map(fn($item) => $item->user?->name ?? 'Unknown')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
