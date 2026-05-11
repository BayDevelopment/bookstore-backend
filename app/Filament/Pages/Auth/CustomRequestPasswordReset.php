<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Filament\Schemas\Schema; // ✅ Ganti Form dengan Schema

class CustomRequestPasswordReset extends RequestPasswordReset
{
    public function request(): void
    {
        $data = $this->form->getState();
        $email = $data['email'];

        $user = User::where('email', $email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'data.email' => 'Email tidak terdaftar dalam sistem.',
            ]);
        }

        if ($user->role !== 'admin') {
            throw ValidationException::withMessages([
                'data.email' => 'Akun tidak sesuai. Anda tidak memiliki akses ke panel admin.',
            ]);
        }

        $status = Password::broker(config('filament.auth.password_broker', 'users'))
            ->sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'data.email' => match ($status) {
                    Password::INVALID_USER => 'Email tidak terdaftar.',
                    Password::RESET_THROTTLED => 'Terlalu banyak percobaan. Silakan tunggu beberapa saat.',
                    default => 'Gagal mengirim link reset password. Silakan coba lagi.',
                },
            ]);
        }

        Notification::make()
            ->title('Link terkirim!')
            ->body('Silakan cek inbox email Anda untuk melanjutkan reset password.')
            ->success()
            ->send();
    }

    public function form(Schema $schema): Schema // ✅ Ganti Form dengan Schema
    {
        return $schema
            ->schema([
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->autocomplete()
                    ->autofocus(),
            ]);
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Lupa Password';
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'Lupa Password?';
    }

    public function getSubheading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return 'Masukkan email Anda dan kami akan kirimkan link untuk reset password.';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getRequestFormAction()
                ->label('Kirim Link Reset'),
        ];
    }

    public function hasFullWidthFormActions(): bool
    {
        return true;
    }
}
