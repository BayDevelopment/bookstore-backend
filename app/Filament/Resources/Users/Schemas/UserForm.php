<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Hamcrest\Core\Set;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Akun')
                    ->icon('heroicon-o-user-circle')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->placeholder('Masukkan nama lengkap')
                            ->helperText('Nama lengkap sesuai identitas resmi.')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('email')
                            ->label('Email')
                            ->placeholder('contoh@email.com')
                            ->helperText('Email digunakan untuk login dan verifikasi.')
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->unique(table: \App\Models\User::class, column: 'email', ignoreRecord: true)
                            ->columnSpan(1),

                        TextInput::make('password')
                            ->label('Password')
                            ->helperText('Minimal 8 karakter. Kosongkan jika tidak ingin mengubah password.')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $operation) => $operation === 'create')
                            ->minLength(8)
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->helperText('Ulangi password yang sama persis.')
                            ->password()
                            ->revealable()
                            ->same('password')
                            ->required(fn(string $operation) => $operation === 'create')
                            ->dehydrated(false)
                            ->maxLength(255)
                            ->columnSpan(1),
                    ]),

                Section::make('Informasi Pribadi')
                    ->icon('heroicon-o-identification')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('nim')
                            ->label('NIM')
                            ->placeholder('Nomor Induk Mahasiswa')
                            ->helperText('NIM bersifat unik, tidak boleh sama dengan mahasiswa lain.')
                            ->nullable()
                            ->maxLength(20)
                            ->unique(table: \App\Models\User::class, column: 'nim', ignoreRecord: true)
                            ->columnSpan(1),

                        TextInput::make('phone')
                            ->label('Nomor HP')
                            ->placeholder('08xxxxxxxxxx')
                            ->helperText('Nomor HP aktif yang bisa dihubungi.')
                            ->nullable()
                            ->tel()
                            ->maxLength(15)
                            ->regex('/^[0-9+\-\s]+$/')
                            ->validationMessages([
                                'regex' => 'Nomor HP hanya boleh berisi angka, +, -, atau spasi.',
                            ])
                            ->columnSpan(1),

                        Select::make('fakultas_id')
                            ->label('Fakultas')
                            ->helperText('Pilih fakultas tempat mahasiswa terdaftar.')
                            ->relationship('fakultas', 'nama_fakultas')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->live()
                            ->afterStateUpdated(fn(Set $set) => $set('prodi_id', null))
                            ->columnSpan(1),

                        Select::make('prodi_id')
                            ->label('Program Studi')
                            ->helperText('Pilih prodi sesuai fakultas yang dipilih.')
                            ->relationship(
                                name: 'prodi',
                                titleAttribute: 'nama_prodi',
                                modifyQueryUsing: fn(\Illuminate\Database\Eloquent\Builder $query, Get $get) =>
                                $query->where('fakultas_id', $get('fakultas_id'))
                            )
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->disabled(fn(Get $get) => blank($get('fakultas_id')))
                            ->helperText(fn(Get $get) => blank($get('fakultas_id'))
                                ? 'Pilih fakultas terlebih dahulu.'
                                : 'Pilih program studi.')
                            ->columnSpan(1),
                    ]),

                Section::make('Hak Akses')
                    ->icon('heroicon-o-shield-check')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('role')
                            ->label('Role')
                            ->helperText('Admin memiliki akses penuh ke panel. Customer hanya bisa menggunakan aplikasi.')
                            ->options([
                                'admin'    => 'Admin',
                                'customer' => 'Customer',
                            ])
                            ->required()
                            ->default('customer')
                            ->native(false)
                            ->columnSpan(1),

                        Placeholder::make('email_verified_at')
                            ->label('Status Verifikasi Email')
                            ->content(fn($record) => $record?->email_verified_at
                                ? '✅ Terverifikasi pada ' . $record->email_verified_at->format('d M Y, H:i')
                                : '❌ Belum terverifikasi')
                            ->columnSpan(1)
                            ->visibleOn('edit'),
                    ]),
            ]);
    }
}
