<?php

namespace App\Filament\Resources\Faculties\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FacultyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ─── FAKULTAS ───────────────────────────────────────────────
                Section::make('Informasi Fakultas')
                    ->description('Data utama fakultas')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('kode_fakultas')
                                    ->label('Kode Fakultas')
                                    ->required()
                                    ->maxLength(10)
                                    ->unique(ignoreRecord: true)
                                    ->placeholder('Contoh: FAK-01')
                                    ->columnSpan(1),

                                TextInput::make('nama_fakultas')
                                    ->label('Nama Fakultas')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Fakultas Teknik')
                                    ->columnSpan(1),
                            ]),

                        Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->maxLength(1000)
                            ->placeholder('Tuliskan deskripsi singkat tentang fakultas ini...')
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger')
                            ->columnSpanFull(),
                    ]),

                // ─── PROGRAM STUDI ───────────────────────────────────────────
                Section::make('Program Studi')
                    ->description('Tambahkan program studi yang ada di fakultas ini')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('prodi')
                            ->relationship('prodi') // sesuaikan nama relasi di model Fakultas
                            ->label('')
                            ->columnSpanFull()
                            ->addActionLabel('+ Tambah Program Studi')
                            ->defaultItems(0)
                            ->collapsible()
                            ->cloneable()
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('kode_prodi')
                                            ->label('Kode Prodi')
                                            ->required()
                                            ->maxLength(10)
                                            ->placeholder('Contoh: TI-01')
                                            ->columnSpan(1),

                                        TextInput::make('nama_prodi')
                                            ->label('Nama Program Studi')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Contoh: Teknik Informatika')
                                            ->columnSpan(1),
                                    ]),
                            ]),
                    ]),
            ]);
    }
}
