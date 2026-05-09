<?php

namespace App\Filament\Resources\OrderReports\Pages;

use App\Filament\Resources\OrderReports\OrderReportResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrderReport extends EditRecord
{
    protected static string $resource = OrderReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
