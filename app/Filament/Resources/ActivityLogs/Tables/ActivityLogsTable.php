<?php

namespace App\Filament\Resources\ActivityLogs\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->width('60px'),

                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable()
                    ->default('—'),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->default('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                BadgeColumn::make('event')
                    ->label('Event')
                    ->colors([
                        'success' => 'register',
                        'info'    => 'login',
                        'warning' => 'logout',
                    ])
                    ->icons([
                        'heroicon-o-user-plus'     => 'register',
                        'heroicon-o-arrow-right-on-rectangle' => 'login',
                        'heroicon-o-arrow-left-on-rectangle'  => 'logout',
                    ])
                    ->sortable(),

                BadgeColumn::make('device_type')
                    ->label('Device')
                    ->colors([
                        'success' => 'desktop',
                        'warning' => 'mobile',
                        'info'    => 'tablet',
                        'gray'    => 'unknown',
                    ])
                    ->icons([
                        'heroicon-o-computer-desktop' => 'desktop',
                        'heroicon-o-device-phone-mobile' => 'mobile',
                        'heroicon-o-device-tablet' => 'tablet',
                    ])
                    ->sortable(),

                TextColumn::make('browser')
                    ->label('Browser')
                    ->searchable()
                    ->formatStateUsing(fn($state, $record) => $state . ($record->browser_version ? ' ' . explode('.', $record->browser_version)[0] : ''))
                    ->icon('heroicon-o-globe-alt'),

                TextColumn::make('platform')
                    ->label('OS / Platform')
                    ->searchable()
                    ->icon('heroicon-o-cpu-chip'),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('IP tersalin!'),

                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->since()          // tampilkan "3 minutes ago"
                    ->tooltip(fn($record) => $record->created_at->format('d M Y H:i:s')),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('Event')
                    ->options([
                        'register' => '✅ Register',
                        'login'    => '🔑 Login',
                        'logout'   => '🚪 Logout',
                    ]),

                SelectFilter::make('device_type')
                    ->label('Device Type')
                    ->options([
                        'desktop' => '🖥 Desktop',
                        'mobile'  => '📱 Mobile',
                        'tablet'  => '📟 Tablet',
                        'unknown' => '❓ Unknown',
                    ]),

                SelectFilter::make('browser')
                    ->label('Browser')
                    ->options([
                        'Chrome'            => 'Chrome',
                        'Firefox'           => 'Firefox',
                        'Safari'            => 'Safari',
                        'Edge'              => 'Edge',
                        'Opera'             => 'Opera',
                        'Samsung Browser'   => 'Samsung Browser',
                        'UC Browser'        => 'UC Browser',
                        'Internet Explorer' => 'Internet Explorer',
                        'Unknown'           => 'Unknown',
                    ])
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([25, 50, 100])

            ->recordActions([])
            ->toolbarActions([]);
    }
}
