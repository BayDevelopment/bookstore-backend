<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Email tersalin!'),

                TextColumn::make('nim')
                    ->label('NIM')
                    ->searchable()
                    ->default('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('phone')
                    ->label('No. HP')
                    ->searchable()
                    ->default('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                BadgeColumn::make('role')
                    ->label('Role')
                    ->colors([
                        'danger'  => 'admin',
                        'success' => 'customer',
                    ])
                    ->icons([
                        'heroicon-o-shield-check' => 'admin',
                        'heroicon-o-user'         => 'customer',
                    ])
                    ->sortable(),

                IconColumn::make('email_verified_at')
                    ->label('Verifikasi Email')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->getStateUsing(fn($record) => !is_null($record->email_verified_at))
                    ->tooltip(fn($record) => $record->email_verified_at
                        ? '✅ Terverifikasi: ' . $record->email_verified_at->format('d M Y, H:i')
                        : '❌ Belum terverifikasi'),

                TextColumn::make('created_at')
                    ->label('Daftar')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->since()
                    ->tooltip(fn($record) => $record->created_at->format('d M Y H:i:s')),
            ])
            ->filters([
                // ── Filter Role ────────────────────────────────────────────
                SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'admin'    => 'Admin',
                        'customer' => 'Customer',
                    ])->columnSpan(2),

                // ── Filter Status Verifikasi ───────────────────────────────
                TernaryFilter::make('email_verified_at')
                    ->label('Status Verifikasi')
                    ->nullable()
                    ->placeholder('Semua')
                    ->trueLabel('Sudah Verifikasi')
                    ->falseLabel('Belum Verifikasi')
                    ->queries(
                        true: fn($query) => $query->whereNotNull('email_verified_at'),
                        false: fn($query) => $query->whereNull('email_verified_at'),
                        blank: fn($query) => $query,
                    )->columnSpan(2),

                // ── Filter Tanggal Daftar ──────────────────────────────────
                Filter::make('created_at')
                    ->label('Tanggal Daftar')
                    ->columnSpanFull()
                    ->form([
                        DatePicker::make('from')
                            ->label('Dari')
                            ->native(false),
                        DatePicker::make('until')
                            ->label('Sampai')
                            ->native(false),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'],  fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('created_at', '<=', $data['until']));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'])  $indicators[] = Indicator::make('Dari: ' . $data['from']);
                        if ($data['until']) $indicators[] = Indicator::make('Sampai: ' . $data['until']);
                        return $indicators;
                    }),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),

                    Action::make('resend_verification')
                        ->label('Kirim Verifikasi Email')
                        ->icon('heroicon-o-envelope')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Kirim Ulang Verifikasi?')
                        ->modalDescription(fn($record) => "Link verifikasi akan dikirim ke {$record->email}.")
                        ->modalSubmitActionLabel('Ya, Kirim')
                        ->visible(fn($record) => is_null($record->email_verified_at))
                        ->action(function ($record) {
                            $record->sendEmailVerificationNotification();

                            Notification::make()
                                ->title('Email Terkirim')
                                ->body("Link verifikasi dikirim ke {$record->email}.")
                                ->success()
                                ->send();
                        }),

                    Action::make('mark_verified')
                        ->label('Tandai Terverifikasi')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Tandai Sebagai Terverifikasi?')
                        ->modalDescription('Email user akan ditandai sudah terverifikasi tanpa mengirim email.')
                        ->modalSubmitActionLabel('Ya, Tandai')
                        ->visible(fn($record) => is_null($record->email_verified_at))
                        ->action(function ($record) {
                            $record->forceFill(['email_verified_at' => now()])->save();

                            Notification::make()
                                ->title('Berhasil')
                                ->body('Email user berhasil ditandai terverifikasi.')
                                ->success()
                                ->send();
                        }),

                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus User?')
                        ->modalDescription('Data user akan dihapus permanen dan tidak bisa dikembalikan.')
                        ->successNotification(
                            Notification::make()
                                ->title('Berhasil')
                                ->body('User berhasil dihapus.')
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
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
