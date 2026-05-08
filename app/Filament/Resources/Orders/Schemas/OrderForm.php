<?php

namespace App\Filament\Resources\Orders\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Section: Order Info
                Section::make('Informasi Order')
                    ->description('Data utama order dari pelanggan')
                    ->schema([

                        Select::make('user_id')
                            ->label('Pelanggan')
                            ->relationship(
                                name: 'user',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn($query) => $query->where('role', 'customer')
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(fn($record) => $record !== null),

                        TextInput::make('total')
                            ->label('Total Harga')
                            ->prefix('Rp')
                            ->readOnly()
                            ->dehydrated(false)
                            ->formatStateUsing(fn($state) => number_format($state ?? 0, 0, ',', '.'))
                            ->disabled(fn($record) => $record?->status === 'completed')
                            ->helperText('Total otomatis dari item buku'),

                        Select::make('status')
                            ->label('Status Order')
                            ->options([
                                'pending'   => 'Pending',
                                'confirmed' => 'Confirmed',
                                'rejected'  => 'Rejected',
                            ])
                            ->default('pending')
                            ->required()
                            ->reactive() // ⬅️ WAJIB biar perubahan langsung update field lain
                            ->disabled(fn($record) => $record?->status === 'completed'),

                        Textarea::make('proof_note')
                            ->label('Catatan Bukti')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('Tambahkan catatan jika bukti ditolak...')
                            ->visible(fn($get) => $get('status') === 'rejected') // ⬅️ tampil hanya saat rejected
                            ->required(fn($get) => $get('status') === 'rejected') // ⬅️ wajib isi kalau rejected
                            ->disabled(fn($record) => $record?->status === 'completed')
                            ->helperText('Wajib diisi jika bukti pembayaran ditolak'),

                    ])->columnSpanFull(),

                // Section: Bukti Pembayaran
                Section::make('Bukti Pembayaran')
                    ->description('Upload dan verifikasi bukti pembayaran pelanggan')
                    ->schema([

                        FileUpload::make('payment_proof')
                            ->label('Bukti Pembayaran')
                            ->image()
                            ->directory('payment_proofs')
                            ->visibility('public') // ← ganti dari 'private' ke 'public'
                            ->maxSize(2048)
                            ->imagePreviewHeight('200')
                            ->openable()           // ← tombol buka gambar di form edit
                            ->downloadable()       // ← tombol download
                            ->helperText('Upload foto/screenshot bukti transfer (maks. 2MB)'),

                        Select::make('proof_status')
                            ->label('Status Bukti')
                            ->options([
                                'not_uploaded' => 'Belum Upload',
                                'uploaded'     => 'Sudah Upload (Menunggu Verifikasi)',
                                'verified'     => 'Terverifikasi',
                                'invalid'      => 'Tidak Valid',
                            ])
                            ->default('not_uploaded')
                            ->required()
                            ->disabled(fn($record) => $record?->status === 'completed')
                            ->helperText('Status verifikasi bukti pembayaran dari admin'),
                    ])->columnSpanFull(),
            ]);
    }
}
