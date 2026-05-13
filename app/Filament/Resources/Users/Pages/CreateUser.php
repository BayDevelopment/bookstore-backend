<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    // ── Konfirmasi sebelum simpan ──────────────────────────────────────────
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->requiresConfirmation()
            ->modalHeading('Buat User Baru?')
            ->modalDescription('Pastikan data yang dimasukkan sudah benar. Setelah disimpan, link verifikasi email akan dikirim ke alamat email yang diinput.')
            ->modalSubmitActionLabel('Ya, Simpan')
            ->modalCancelActionLabel('Batal');
    }

    // ── Kirim email verifikasi setelah user dibuat ─────────────────────────
    protected function afterCreate(): void
    {
        $user = $this->record;

        // Kirim link verifikasi email
        $user->sendEmailVerificationNotification();

        Notification::make()
            ->title('User Berhasil Dibuat')
            ->body("Link verifikasi email telah dikirim ke {$user->email}.")
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Berhasil')
            ->body('Data user berhasil ditambahkan.')
            ->success();
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->label('Create')
                ->icon('heroicon-o-check-circle')
                ->color('primary'),

            $this->getCreateAnotherFormAction()
                ->label('Create & Create Another')
                ->icon('heroicon-o-plus-circle')
                ->color('success'),

            $this->getCancelFormAction()
                ->label('Cancel')
                ->url($this->getResource()::getUrl('index'))
                ->icon('heroicon-o-x-mark')
                ->color('gray'),
        ];
    }
}
