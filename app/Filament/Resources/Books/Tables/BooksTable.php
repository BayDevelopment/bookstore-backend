<?php

namespace App\Filament\Resources\Books\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Builder;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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

                BadgeColumn::make('type')
                    ->label('Tipe')
                    ->colors([
                        'success' => 'fisik',
                        'primary' => 'digital',
                        'warning' => 'keduanya',
                    ])
                    ->formatStateUsing(fn($state) => match ($state) {
                        'fisik'    => '📦 Fisik',
                        'digital'  => '💻 Digital',
                        'keduanya' => '📦💻 Keduanya',
                        default    => $state,
                    }),

                TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state === 0  => 'danger',
                        $state <= 10  => 'warning',
                        default       => 'success',
                    }),

                IconColumn::make('file_path')
                    ->label('PDF')
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

                SelectFilter::make('type')
                    ->label('Tipe Buku')
                    ->options([
                        'fisik'    => '📦 Fisik',
                        'digital'  => '💻 Digital',
                        'keduanya' => '📦💻 Keduanya',
                    ]),

                Filter::make('stok_habis')
                    ->label('Stok Habis')
                    ->query(fn(Builder $query) => $query->where('stock', 0)),

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
                        ->modalDescription('Data akan dipindahkan ke trash (bisa dikembalikan).')
                        ->successNotification(
                            Notification::make()
                                ->title('Berhasil')
                                ->body('Data berhasil dihapus (soft delete)')
                                ->success()
                        ),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-o-ellipsis-vertical')
                    ->button()
                    ->outlined()
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
