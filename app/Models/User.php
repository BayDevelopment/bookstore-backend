<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait; // ✅
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'nim', 'phone', 'fakultas_id', 'prodi_id', 'role'])]
#[Hidden(['password', 'remember_token'])]

class User extends Authenticatable implements FilamentUser, MustVerifyEmail, CanResetPassword
{
    use HasApiTokens, HasFactory, Notifiable, CanResetPasswordTrait; // ✅

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function canAccessPanel(\Filament\Panel $panel): bool
    {
        return $this->role === 'admin';
    }

    public function orders()
    {
        return $this->hasMany(\App\Models\OrderModel::class, 'user_id');
    }

    public function carts()
    {
        return $this->hasMany(\App\Models\CartModel::class, 'user_id');
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
