<?php

namespace App\Filament\Widgets;

use App\Models\OrderModel;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RevenueChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected ?string $heading = 'Revenue Chart';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $dates = $this->getDateRange();

        $revenues = DB::table('orders')
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->where('status', 'confirmed')
            ->whereIn(DB::raw('DATE(created_at)'), $dates->toArray())
            ->groupBy('date')
            ->pluck('total', 'date');

        return [
            'labels'   => $dates->map(fn($d) => Carbon::parse($d)->format('d M'))->values()->toArray(),
            'datasets' => [[
                'label' => 'Pendapatan',
                'data'  => $dates->map(fn($d) => (float) ($revenues[$d] ?? 0))->values()->toArray(),
            ]],
        ];
    }

    private function getDateRange(): \Illuminate\Support\Collection
    {
        return match ($this->filters['period'] ?? 'today') {
            'yesterday'  => collect([now()->subDay()->toDateString()]),
            'this_month' => collect(range(0, now()->daysInMonth - 1))->map(fn($d) => now()->startOfMonth()->addDays($d)->toDateString()),
            'last_month' => (function () {
                $start = now()->subMonthNoOverflow()->startOfMonth();
                return collect(range(0, $start->daysInMonth - 1))->map(fn($d) => $start->copy()->addDays($d)->toDateString());
            })(),
            'this_year'  => collect(range(0, now()->month - 1))->map(fn($m) => now()->startOfYear()->addMonths($m)->format('Y-m-01')),
            'last_year'  => collect(range(0, 11))->map(fn($m) => now()->subYear()->startOfYear()->addMonths($m)->format('Y-m-01')),
            default      => collect(range(6, 0))->map(fn($d) => now()->subDays($d)->toDateString()), // today = 7 hari terakhir
        };
    }

    private function getLabelFormat(): string
    {
        return match ($this->filters['period'] ?? 'today') {
            'this_year', 'last_year' => 'M Y',
            default                  => 'd M',
        };
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'tooltip' => [
                    'callbacks' => [
                        'label' => "function(context) { return 'Rp ' + context.raw.toLocaleString('id-ID'); }",
                    ],
                ],
            ],
            'scales' => [
                'y' => [
                    'ticks' => [
                        'callback' => "function(value) { return 'Rp ' + value.toLocaleString('id-ID'); }",
                    ],
                ],
            ],
        ];
    }
}
