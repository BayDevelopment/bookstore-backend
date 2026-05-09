<?php

namespace App\Filament\Widgets;

use App\Models\BookModel;
use App\Models\OrderModel;
use App\Models\User;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsDashboard extends StatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected int | string | array $columnSpan = 'full';

    /**
     * Terapkan filter periode ke query yang diberikan.
     */
    private function applyDateFilter(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        // Ambil nilai period dari filter Dashboard page
        $period = $this->filters['period'] ?? 'today';

        return match ($period) {
            'today' => $query->whereDate('created_at', now()->toDateString()),

            'yesterday' => $query->whereDate('created_at', now()->subDay()->toDateString()),

            'this_month' => $query
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year),

            'last_month' => (function () use ($query) {
                $lastMonth = now()->subMonthNoOverflow();
                return $query
                    ->whereMonth('created_at', $lastMonth->month)
                    ->whereYear('created_at', $lastMonth->year);
            })(),

            'this_year' => $query->whereYear('created_at', now()->year),

            'last_year' => $query->whereYear('created_at', now()->subYearNoOverflow()->year),

            default => $query->whereDate('created_at', now()->toDateString()),
        };
    }

    protected function getStats(): array
    {
        $confirmed = fn() => OrderModel::query()->where('status', 'confirmed');

        return [
            Stat::make('Jumlah Buku', $this->applyDateFilter(BookModel::query())->count())
                ->description('Total buku')
                ->color('primary'),

            Stat::make('Customer', $this->applyDateFilter(User::query())->count())
                ->description('Total customer')
                ->color('success'),

            Stat::make('Total Order', $this->applyDateFilter($confirmed())->count())
                ->description('Order confirmed')
                ->color('info'),

            Stat::make('Pendapatan', 'Rp ' . number_format($this->applyDateFilter($confirmed())->sum('total'), 0, ',', '.'))
                ->description('Dari order confirmed')
                ->color('warning')
                ->extraAttributes(['style' => 'font-size: 1.2rem; font-weight: 600;']),
        ];
    }
}
