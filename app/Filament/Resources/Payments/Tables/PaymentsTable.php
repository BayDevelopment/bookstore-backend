<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Metode')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->copyable()
                    ->tooltip('Klik untuk copy'),

                // 🔥 Status tipe (manual vs gateway)
                TextColumn::make('type')
                    ->label('Jenis')
                    ->getStateUsing(
                        fn($record) =>
                        $record->midtrans_payment_type ? 'Gateway' : 'Manual'
                    )
                    ->badge()
                    ->color(
                        fn($state) =>
                        $state === 'Gateway' ? 'success' : 'warning'
                    ),

                TextColumn::make('midtrans_payment_type')
                    ->label('Midtrans')
                    ->badge()
                    ->getStateUsing(
                        fn($record) =>
                        $record->midtrans_payment_type ?: 'No Midtrans'
                    )
                    ->color(
                        fn($state) =>
                        $state === 'No Midtrans' ? 'danger' : 'success'
                    ),

                // 🔥 Info rekening (kalau ada)
                TextColumn::make('account_number')
                    ->label('Rekening')
                    ->formatStateUsing(fn($state) => $state ? '****' . substr($state, -4) : '-')
                    ->tooltip(
                        fn($record) =>
                        $record->account_name
                            ? "{$record->bank_name} - {$record->account_name}"
                            : null
                    ),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since() // 🔥 lebih readable (e.g. "2 jam lalu")
                    ->sortable(),
            ])

            ->filters([
                SelectFilter::make('midtrans_payment_type')
                    ->label('Tipe Midtrans')
                    ->options([
                        'bank_transfer'  => 'Bank Transfer',
                        'gopay'          => 'GoPay',
                        'qris'           => 'QRIS',
                        'shopeepay'      => 'ShopeePay',
                        'credit_card'    => 'Kartu Kredit',
                    ])
                    ->placeholder('Semua'),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif')
                    ->placeholder('Semua'),
            ])

            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),

                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus metode pembayaran?')
                        ->modalDescription('Data akan dihapus.')
                        ->successNotification(
                            Notification::make()
                                ->title('Berhasil')
                                ->body('Metode pembayaran berhasil dihapus')
                                ->success()
                        ),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->button()
                    ->outlined(),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
