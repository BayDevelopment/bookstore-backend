<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Metode Pembayaran')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Metode')
                            ->required()
                            ->maxLength(100)
                            ->helperText('Contoh: Transfer BCA, QRIS, COD')
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn($state, callable $set) =>
                                $set('code', Str::slug(trim($state)))
                            ),

                        TextInput::make('code')
                            ->label('Kode Unik')
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50)
                            ->regex('/^[a-z0-9\-]+$/')
                            ->helperText('Otomatis dari nama. Hanya huruf kecil, angka, dan tanda -')
                            ->validationMessages([
                                'regex' => 'Kode hanya boleh huruf kecil, angka, dan tanda strip (-).',
                                'unique' => 'Kode ini sudah digunakan.',
                            ]),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->placeholder('Penjelasan singkat metode pembayaran ini...')
                            ->nullable()
                            ->rows(3)
                            ->maxLength(500)
                            ->helperText('Opsional. Maksimal 500 karakter.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Rekening (Manual Transfer)')
                    ->description('Wajib diisi jika tidak menggunakan Midtrans.')
                    ->schema([
                        TextInput::make('bank_name')
                            ->label('Nama Bank')
                            ->maxLength(50)
                            ->helperText('Contoh: BCA, Mandiri, BNI')
                            ->required(fn($get) => !$get('midtrans_payment_type'))
                            ->validationMessages([
                                'required' => 'Nama bank wajib diisi untuk transfer manual.',
                            ]),

                        TextInput::make('account_number')
                            ->label('Nomor Rekening')
                            ->numeric()
                            ->maxLength(30)
                            ->helperText('Masukkan hanya angka, tanpa spasi atau simbol.')
                            ->required(fn($get) => !$get('midtrans_payment_type'))
                            ->rule('digits_between:6,30')
                            ->validationMessages([
                                'required' => 'Nomor rekening wajib diisi.',
                                'digits_between' => 'Nomor rekening harus 6–30 digit angka.',
                            ])
                            ->dehydrateStateUsing(fn($state) => preg_replace('/\D/', '', $state)), // sanitasi

                        TextInput::make('account_name')
                            ->label('Atas Nama')
                            ->maxLength(100)
                            ->helperText('Nama pemilik rekening.')
                            ->required(fn($get) => !$get('midtrans_payment_type'))
                            ->validationMessages([
                                'required' => 'Nama pemilik rekening wajib diisi.',
                            ]),
                    ])
                    ->columns(1),

                Section::make('Konfigurasi Midtrans')
                    ->description('Gunakan ini jika pembayaran via gateway otomatis.')
                    ->schema([
                        Select::make('midtrans_payment_type')
                            ->label('Tipe Pembayaran Midtrans')
                            ->placeholder('— Tidak menggunakan Midtrans —')
                            ->nullable()
                            ->options([
                                'bank_transfer'  => 'Bank Transfer',
                                'qris'           => 'QRIS',
                            ])
                            ->helperText('Jika dipilih, field rekening manual akan diabaikan.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Nonaktifkan jika metode tidak ingin ditampilkan ke customer.')
                            ->default(true),
                    ]),
            ]);
    }
}
