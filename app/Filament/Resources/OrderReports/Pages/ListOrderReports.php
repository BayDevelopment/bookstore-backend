<?php

namespace App\Filament\Resources\OrderReports\Pages;

use App\Filament\Resources\OrderReports\OrderReportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOrderReports extends ListRecords
{
    protected static string $resource = OrderReportResource::class;
}
