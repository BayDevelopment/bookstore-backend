<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Select;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Actions\FilterAction;
use Filament\Pages\Dashboard\Concerns\HasFiltersAction;

class Dashboard extends BaseDashboard
{
    use HasFiltersAction;

    protected function getHeaderActions(): array
    {
        return [
            FilterAction::make()
                ->label('Filter Periode')
                ->icon('heroicon-o-funnel')
                ->form([
                    Select::make('period')
                        ->label('Periode')
                        ->options([
                            'today'      => 'Hari Ini',
                            'yesterday'  => 'Kemarin',
                            'this_month' => 'Bulan Ini',
                            'last_month' => 'Bulan Kemarin',
                            'this_year'  => 'Tahun Ini',
                            'last_year'  => 'Tahun Kemarin',
                        ])
                        ->default('today')
                        ->required(),
                ]),
        ];
    }
}
