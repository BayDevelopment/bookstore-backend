<?php

namespace App\Filament\Resources\Books\Schemas;

use App\Models\CategoriesModel;
use App\Models\FakultasModel;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                Section::make('Informasi Buku')
                    ->description('Isi data utama buku dengan lengkap dan benar.')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul Buku')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Algoritma & Pemrograman Dasar')
                                    ->helperText('Masukkan judul buku secara lengkap sesuai cover buku.')
                                    ->columnSpan(1),

                                TextInput::make('author')
                                    ->label('Penulis')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Contoh: Dr. Budi Santoso, M.Kom')
                                    ->helperText('Nama penulis atau editor buku. Jika lebih dari satu, pisahkan dengan koma.')
                                    ->columnSpan(1),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('category_id')
                                    ->label('Kategori')
                                    ->relationship('category', 'name')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->disabled(fn() => DB::table('categories')->count() === 0)
                                    ->helperText(
                                        fn() => DB::table('categories')->count() === 0
                                            ? '⚠️ Belum ada kategori. Tambah di menu Kategori terlebih dahulu.'
                                            : 'Pilih kategori yang paling sesuai dengan isi buku.'
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
                                            ? '⚠️ Belum ada fakultas. Tambah di menu Fakultas terlebih dahulu.'
                                            : 'Pilih fakultas yang menggunakan buku ini sebagai referensi.'
                                    )
                                    ->columnSpan(1),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('type')
                                    ->label('Tipe Buku')
                                    ->required()
                                    ->live() // ← reactive
                                    ->options([
                                        'cetak'   => '📦 Buku Cetak',
                                        'digital' => '💻 Buku Digital (PDF)',
                                    ])
                                    ->helperText('Pilih tipe buku. Buku cetak wajib isi stok, buku digital wajib upload file PDF.')
                                    ->columnSpan(1),

                                // Stok — hanya tampil jika cetak
                                TextInput::make('stock')
                                    ->label('Stok')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->placeholder('Contoh: 50')
                                    ->helperText('Jumlah stok buku cetak yang tersedia.')
                                    ->visible(fn(Get $get) => $get('type') === 'cetak')
                                    ->required(fn(Get $get) => $get('type') === 'cetak')
                                    ->columnSpan(1),

                                // Harga — selalu tampil
                                TextInput::make('price')
                                    ->label('Harga')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->minValue(0)
                                    ->placeholder('Contoh: 75000')
                                    ->helperText('Masukkan harga dalam Rupiah tanpa titik atau koma.')
                                    ->columnSpan(1),
                            ]),
                    ]),

                Section::make('Media Buku')
                    ->description('Upload cover dan file digital buku jika tersedia.')
                    ->columnSpanFull()
                    ->schema([
                        // Cover — selalu tampil (cetak & digital sama-sama butuh)
                        FileUpload::make('cover')
                            ->label('Cover Buku')
                            ->image()
                            ->maxSize(2048)
                            ->directory('books/covers')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->helperText('Upload foto cover buku. Format: JPG, PNG, WEBP. Maksimal 2MB.')
                            ->columnSpanFull(),

                        // File PDF — hanya tampil jika digital
                        FileUpload::make('file_path')
                            ->label('File Digital (PDF)')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(51200)
                            ->directory('books/files')
                            ->helperText('Upload file PDF buku. Maksimal 50MB.')
                            ->visible(fn(Get $get) => $get('type') === 'digital')
                            ->required(fn(Get $get) => $get('type') === 'digital')
                            ->columnSpanFull(),
                    ]),

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
                            ->placeholder('Tuliskan sinopsis, daftar isi, atau informasi tambahan tentang buku ini...')
                            ->helperText('Deskripsikan isi buku secara singkat. Minimal 50 karakter agar pembeli lebih tertarik.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
