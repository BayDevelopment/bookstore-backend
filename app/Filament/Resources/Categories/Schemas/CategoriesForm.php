<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CategoriesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Kategori')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Contoh: Teknik Informatika')
                    ->autofocus(),

                TextInput::make('icon')
                    ->label('Icon')
                    ->placeholder('Contoh: BookOpenIcon')
                    ->nullable()
                    ->maxLength(100)
                    ->helperText('Gunakan nama icon Heroicons, contoh: BookOpenIcon')
                    ->rule('regex:/^[A-Za-z]+Icon$/')
                    ->helperText('Contoh : BookOpenIcon'),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->inline(false),
            ]);
    }
}
