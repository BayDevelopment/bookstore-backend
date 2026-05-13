<?php

namespace App\Filament\Resources\OrderReports;

use App\Filament\Resources\OrderReports\Pages\ListOrderReports;
use App\Filament\Resources\OrderReports\Tables\OrderReportsTable;
use App\Models\OrderItemModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class OrderReportResource extends Resource
{
    protected static ?string $model = OrderItemModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Laporan Penjualan';
    protected static ?int    $navigationSort  = 1;

    public static function canCreate(): bool
    {
        return false;
    }
    public static function canEdit($record): bool
    {
        return false;
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Transaksi';
    }
    public static function getNavigationSort(): ?int
    {
        return 2; // ganti angka sesuai urutan yang lo mau
    }

    public static function getModelLabel(): string
    {
        return 'Laporan Penjualan';
    }
    public static function getPluralModelLabel(): string
    {
        return 'Data Laporan';
    }

    public static function table(Table $table): Table
    {
        return OrderReportsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrderReports::route('/'),
        ];
    }
}
