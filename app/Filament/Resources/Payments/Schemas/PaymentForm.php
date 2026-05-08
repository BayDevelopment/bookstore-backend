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
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn($state, callable $set) =>
                                $set('code', Str::slug($state))
                            ),

                        TextInput::make('code')
                            ->label('Kode Unik')
                            ->disabled()
                            ->dehydrated() // tetap tersimpan ke database
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),

                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->placeholder('Penjelasan singkat metode pembayaran ini...')
                            ->nullable()
                            ->rows(3)
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Konfigurasi Midtrans')
                    ->description('Kosongkan jika metode ini manual / bayar di tempat.')
                    ->schema([
                        Select::make('midtrans_payment_type')
                            ->label('Tipe Pembayaran Midtrans')
                            ->placeholder('— Tidak menggunakan Midtrans —')
                            ->nullable()
                            ->options([
                                'bank_transfer'  => 'Bank Transfer',
                                'qris'           => 'QRIS',
                            ])
                            ->helperText('Sesuai payment_type Midtrans API.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Matikan untuk menyembunyikan dari pilihan customer.')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}
