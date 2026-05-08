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
                    ->sortable(),

                TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->color('primary')
                    ->searchable(),

                TextColumn::make('midtrans_payment_type')
                    ->label('Tipe Midtrans')
                    ->placeholder('-')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'gray'),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
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
                        'cstore'         => 'Convenience Store',
                        'echannel'       => 'Mandiri Bill',
                        'bca_klikbca'    => 'KlikBCA',
                        'bca_klikpay'    => 'BCA KlikPay',
                        'cimb_clicks'    => 'CIMB Clicks',
                        'danamon_online' => 'Danamon Online',
                        'uob_ezpay'      => 'UOB EZPay',
                    ])
                    ->placeholder('Semua'),

                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Nonaktif')
                    ->placeholder('Semua'),
            ])
            ->recordActions([
                ActionGroup::make(
                    [
                        EditAction::make(),

                        DeleteAction::make()
                            ->requiresConfirmation()
                            ->modalHeading('Hapus data?')
                            ->modalDescription('Data akan dipindahkan ke trash (bisa dikembalikan).')
                            ->successNotification(
                                Notification::make()
                                    ->title('Berhasil')
                                    ->body('Data berhasil dihapus (soft delete)')
                                    ->success()
                            ),
                    ]
                )->label('Aksi')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->button()
                    ->outlined()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
