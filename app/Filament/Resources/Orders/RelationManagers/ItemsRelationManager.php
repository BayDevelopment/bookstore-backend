<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\BookModel;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Item Buku'; // ✅ tambah title

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('book_id')
                    ->label('Buku')
                    ->options(BookModel::query()->pluck('title', 'id'))
                    ->searchable()
                    ->required()
                    ->validationMessages([
                        'required' => 'Buku wajib dipilih.',
                    ])
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                        if (!$state) return;
                        $book = BookModel::query()->find((int) $state);
                        if ($book) {
                            $set('price', $book->price);
                        }
                    })
                    ->helperText('Pilih buku yang dipesan'),

                TextInput::make('qty')
                    ->label('Jumlah')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->default(1)
                    ->live()
                    ->validationMessages([
                        'required' => 'Jumlah wajib diisi.',
                        'min'      => 'Jumlah minimal 1.',
                        'numeric'  => 'Jumlah harus berupa angka.',
                    ])
                    ->rules([
                        fn(Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                            if (!$value) {
                                $fail('Jumlah wajib diisi.');
                                return;
                            }
                            if ((int) $value < 1) {
                                $fail('Jumlah minimal 1.');
                                return;
                            }
                            $bookId = $get('book_id');
                            $book = $bookId ? BookModel::query()->find((int) $bookId) : null;
                            if (!$book) {
                                $fail('Pilih buku terlebih dahulu.');
                                return;
                            }
                            if ($book->stock === 0) {
                                $fail('Stok buku ini habis, tidak bisa dipesan.');
                                return;
                            }
                            if ((int) $value > $book->stock) {
                                $fail("Jumlah melebihi stok tersedia ({$book->stock} tersisa).");
                                return;
                            }
                        },
                    ])
                    ->helperText(function (Get $get) {
                        $bookId = $get('book_id');
                        $book = $bookId ? BookModel::query()->find((int) $bookId) : null;

                        if (!$book) return 'Jumlah buku yang dipesan';

                        if ($book->stock === 0) {
                            return new \Illuminate\Support\HtmlString(
                                '<span style="color: red; font-weight: bold;">⚠️ Stok habis, tidak bisa dipesan!</span>'
                            );
                        }

                        return new \Illuminate\Support\HtmlString(
                            "<span style=\"color: #16a34a;\">✅ Stok tersedia: <strong>{$book->stock}</strong></span>"
                        );
                    }),

                TextInput::make('price')
                    ->label('Harga Satuan')
                    ->numeric()
                    ->prefix('Rp')
                    ->required()
                    ->readOnly()
                    ->validationMessages([
                        'required' => 'Harga wajib terisi, pilih buku terlebih dahulu.',
                    ])
                    ->helperText('Harga otomatis dari data buku'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('book_id')
            ->columns([
                TextColumn::make('book.title')
                    ->label('Judul Buku')
                    ->searchable()
                    ->description(fn($record) => $record->book?->author),

                TextColumn::make('qty')
                    ->label('Jumlah')
                    ->alignCenter(),

                TextColumn::make('price')
                    ->label('Harga Satuan')
                    ->money('IDR'),

                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->getStateUsing(fn($record) => $record->qty * $record->price),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Buku')
                    ->icon('heroicon-o-plus')
                    ->successNotification(null)
                    ->modalSubmitAction(
                        fn($action) => $action
                            ->label('Simpan')
                            ->icon('heroicon-o-check-circle')
                            ->color('success')
                    )
                    ->modalCancelAction(
                        fn($action) => $action
                            ->label('Batal')
                            ->icon('heroicon-o-x-mark')
                            ->color('gray')
                    )
                    ->modalFooterActionsAlignment(\Filament\Support\Enums\Alignment::Between)
                    ->extraModalFooterActions([
                        \Filament\Actions\Action::make('createAnother')
                            ->label('Simpan & Tambah Lagi')
                            ->icon('heroicon-o-plus-circle')
                            ->color('gray')
                            ->action(fn() => null), // handle manual jika perlu
                    ])
                    ->after(function () {
                        $this->updateOrderTotal();
                        Notification::make()
                            ->title('Buku Berhasil Ditambahkan')
                            ->body('Item baru telah ditambahkan dan total order diperbarui.')
                            ->success()
                            ->icon('heroicon-o-shopping-cart')
                            ->duration(4000)
                            ->send();
                    }),
            ])
            // ✅ Ganti actions -> recordActions (referensi dari ProductImagesRelationManager)
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->successNotification(null)
                        ->after(function () {
                            $this->updateOrderTotal();
                            Notification::make()
                                ->title('Item Berhasil Diperbarui')
                                ->body('Perubahan item telah disimpan dan total order diperbarui.')
                                ->success()
                                ->icon('heroicon-o-pencil-square')
                                ->duration(4000)
                                ->send();
                        }),

                    DeleteAction::make()
                        ->successNotification(null)
                        ->after(function () {
                            $this->updateOrderTotal();
                            Notification::make()
                                ->title('Item Dihapus')
                                ->body('Item telah dihapus dari order dan total diperbarui.')
                                ->danger()
                                ->icon('heroicon-o-trash')
                                ->duration(4000)
                                ->send();
                        }),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Aksi')
                    ->color('gray'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->after(function () {
                            $this->updateOrderTotal();
                            Notification::make()
                                ->title('Item Terpilih Dihapus')
                                ->body('Semua item yang dipilih telah dihapus dan total diperbarui.')
                                ->danger()
                                ->icon('heroicon-o-trash')
                                ->duration(4000)
                                ->send();
                        }),
                ]),
            ]);
    }

    protected function updateOrderTotal(): void
    {
        $order = $this->getOwnerRecord();
        $total = $order->items()->sum(DB::raw('qty * price'));
        $order->update(['total' => $total]);
    }
}
