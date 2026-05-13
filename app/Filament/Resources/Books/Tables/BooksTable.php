<?php

namespace App\Filament\Resources\Books\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BooksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover')
                    ->label('Cover')
                    ->width(50)
                    ->height(70)
                    ->defaultImageUrl(asset('images/no_cover.jpg'))
                    ->extraImgAttributes(['class' => 'rounded-md object-cover']),

                TextColumn::make('title')
                    ->label('Judul Buku')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->weight(FontWeight::Medium)
                    ->description(fn($record) => $record->author),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fakultas.nama_fakultas')
                    ->label('Fakultas')
                    ->badge()
                    ->color('warning')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                // ✅ Tipe: derived dari has_print & has_pdf
                TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->getStateUsing(fn($record) => match (true) {
                        $record->has_print && $record->has_pdf => 'keduanya',
                        $record->has_print                    => 'cetak',
                        $record->has_pdf                      => 'digital',
                        default                               => 'tidak ada',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'cetak'     => '📦 Cetak',
                        'digital'   => '💻 Digital',
                        'keduanya'  => '📦💻 Keduanya',
                        default     => '⚠️ Tidak Ada',
                    })
                    ->color(fn($state) => match ($state) {
                        'cetak'    => 'success',
                        'digital'  => 'primary',
                        'keduanya' => 'warning',
                        default    => 'danger',
                    }),

                // ✅ Harga Cetak
                TextColumn::make('price_print')
                    ->label('Harga Cetak')
                    ->money('IDR')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

                // ✅ Harga PDF
                TextColumn::make('price_pdf')
                    ->label('Harga PDF')
                    ->money('IDR')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(),

                // ✅ Stok — hanya relevan untuk cetak
                TextColumn::make('stock')
                    ->label('Stok Cetak')
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state === 0  => 'danger',
                        $state <= 10  => 'warning',
                        default       => 'success',
                    })
                    ->placeholder('-'),

                // ✅ Ada file PDF atau tidak
                IconColumn::make('file_path')
                    ->label('File PDF')
                    ->boolean()
                    ->trueIcon('heroicon-o-document-text')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->getStateUsing(fn($record) => !empty($record->file_path)),

                TextColumn::make('created_at')
                    ->label('Ditambahkan')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('fakultas_id')
                    ->label('Fakultas')
                    ->relationship('fakultas', 'nama_fakultas')
                    ->searchable()
                    ->preload(),

                // ✅ Filter tipe berdasarkan has_print & has_pdf
                Filter::make('cetak')
                    ->label('📦 Hanya Cetak')
                    ->query(fn(Builder $query) => $query->where('has_print', true)),

                Filter::make('digital')
                    ->label('💻 Hanya Digital')
                    ->query(fn(Builder $query) => $query->where('has_pdf', true)),

                Filter::make('keduanya')
                    ->label('📦💻 Cetak & Digital')
                    ->query(fn(Builder $query) => $query->where('has_print', true)->where('has_pdf', true)),

                Filter::make('stok_habis')
                    ->label('Stok Habis')
                    ->query(fn(Builder $query) => $query->where('stock', 0)->where('has_print', true)),

                Filter::make('stok_menipis')
                    ->label('Stok Menipis (≤ 10)')
                    ->query(fn(Builder $query) => $query->where('stock', '<=', 10)->where('stock', '>', 0)),

                Filter::make('ada_pdf')
                    ->label('Ada File PDF')
                    ->query(fn(Builder $query) => $query->whereNotNull('file_path')),

                Filter::make('tanpa_pdf')
                    ->label('Tanpa File PDF')
                    ->query(fn(Builder $query) => $query->whereNull('file_path')),
            ])

            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),

                    DeleteAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus data?')
                        ->modalDescription('Data akan dipindahkan ke trash.')
                        ->successNotification(
                            Notification::make()
                                ->title('Berhasil')
                                ->body('Data berhasil dihapus')
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
            ]);
    }
}
