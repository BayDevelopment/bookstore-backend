<?php

namespace App\Filament\Resources\Books\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class BookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ── INFORMASI UTAMA ──
                Section::make('Informasi Buku')
                    ->description('Isi data utama buku dengan lengkap dan benar.')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('title')
                                ->label('Judul Buku')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Contoh: Algoritma & Pemrograman Dasar')
                                ->columnSpan(1),

                            TextInput::make('author')
                                ->label('Penulis')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Contoh: Dr. Budi Santoso, M.Kom')
                                ->columnSpan(1),
                        ]),

                        Grid::make(2)->schema([
                            Select::make('category_id')
                                ->label('Kategori')
                                ->relationship('category', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->disabled(fn() => DB::table('categories')->count() === 0)
                                ->helperText(
                                    fn() => DB::table('categories')->count() === 0
                                        ? '⚠️ Belum ada kategori.'
                                        : 'Pilih kategori yang sesuai.'
                                )
                                ->columnSpan(1),

                            Select::make('fakultas_id')
                                ->label('Fakultas')
                                ->relationship('fakultas', 'nama_fakultas')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->disabled(fn() => DB::table('fakultas')->count() === 0)
                                ->helperText(
                                    fn() => DB::table('fakultas')->count() === 0
                                        ? '⚠️ Belum ada fakultas.'
                                        : 'Pilih fakultas yang menggunakan buku ini.'
                                )
                                ->columnSpan(1),
                        ]),
                    ]),

                // ── TIPE CETAK ──
                Section::make('📦 Buku Cetak')
                    ->description('Aktifkan jika buku ini tersedia dalam versi cetak.')
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('has_print')
                            ->label('Tersedia versi cetak')
                            ->live()
                            ->default(false),

                        Grid::make(2)
                            ->visible(fn(Get $get) => $get('has_print'))
                            ->schema([
                                TextInput::make('price_print')
                                    ->label('Harga Cetak')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->minValue(0)
                                    ->required(fn(Get $get) => $get('has_print'))
                                    ->placeholder('Contoh: 115000')
                                    ->columnSpan(1),

                                TextInput::make('stock')
                                    ->label('Stok')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required(fn(Get $get) => $get('has_print'))
                                    ->placeholder('Contoh: 50')
                                    ->columnSpan(1),
                            ]),
                    ]),

                // ── TIPE DIGITAL ──
                Section::make('💻 Buku Digital (PDF)')
                    ->description('Aktifkan jika buku ini tersedia dalam versi PDF.')
                    ->columnSpanFull()
                    ->schema([
                        Toggle::make('has_pdf')
                            ->label('Tersedia versi PDF')
                            ->live()
                            ->default(false),

                        Grid::make(2)
                            ->visible(fn(Get $get) => $get('has_pdf'))
                            ->schema([
                                TextInput::make('price_pdf')
                                    ->label('Harga PDF')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->minValue(0)
                                    ->required(fn(Get $get) => $get('has_pdf'))
                                    ->placeholder('Contoh: 100000')
                                    ->columnSpan(1),

                                // Kolom kosong untuk keseimbangan grid
                                Grid::make(1)->columnSpan(1)->schema([]),
                            ]),

                        FileUpload::make('file_path')
                            ->label('File PDF')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(5000)
                            ->directory('books/files')
                            ->visibility('private')
                            ->helperText('Upload file PDF. Maksimal 5MB.')
                            ->visible(fn(Get $get) => $get('has_pdf'))
                            ->required(fn(Get $get) => $get('has_pdf'))
                            ->columnSpanFull(),
                    ]),

                // ── MEDIA ──
                Section::make('Media')
                    ->columnSpanFull()
                    ->schema([
                        FileUpload::make('cover')
                            ->label('Cover Buku')
                            ->image()
                            ->maxSize(2048)
                            ->directory('books/covers')
                            ->acceptedFileTypes(['image/jpeg', 'image/png'])
                            ->helperText('Format: JPG, PNG. Maksimal 2MB.')
                            ->columnSpanFull(),
                    ]),

                // ── DESKRIPSI ──
                Section::make('Deskripsi')
                    ->columnSpanFull()
                    ->schema([
                        RichEditor::make('description')
                            ->label('Deskripsi Buku')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                                'undo',
                                'redo',
                            ])
                            ->placeholder('Tuliskan sinopsis atau informasi tambahan...')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
