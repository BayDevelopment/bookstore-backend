<?php

namespace App\Filament\Resources\OrderReports\Tables;

use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class OrderReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order.user.name')
                    ->label('Customer')
                    ->searchable(),

                TextColumn::make('book.title')
                    ->label('Produk')
                    ->searchable(),

                TextColumn::make('qty')
                    ->label('Qty')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('order.status')
                    ->label('Status Order')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'pending'   => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    }),

                TextColumn::make('order.proof_status')
                    ->label('Status Bukti')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),

                TextColumn::make('order.total')
                    ->label('Total')
                    ->money('IDR'),

                TextColumn::make('created_at')
                    ->label('Tanggal Beli')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status Order')
                    ->options([
                        'pending'   => 'Pending',
                        'confirmed' => 'Confirmed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->query(
                        fn($query, array $data) =>
                        $query->when(
                            $data['value'],
                            fn($q) => $q->whereHas('order', fn($q) => $q->where('status', $data['value']))
                        )
                    ),

                SelectFilter::make('proof_status')
                    ->label('Status Bukti')
                    ->options([
                        'pending'  => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->query(
                        fn($query, array $data) =>
                        $query->when(
                            $data['value'],
                            fn($q) => $q->whereHas('order', fn($q) => $q->where('proof_status', $data['value']))
                        )
                    ),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(
                        fn($query, array $data) =>
                        $query
                            ->when($data['from'],  fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('created_at', '<=', $data['until']))
                    ),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Export Excel')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename('laporan-penjualan-' . now()->format('d-m-Y')),
                    ]),
            ])
            ->recordActions([])
            ->toolbarActions([]);
    }
}
