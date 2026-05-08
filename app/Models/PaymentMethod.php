<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'midtrans_payment_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Scope: hanya yang aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper: cek apakah metode ini cash/manual
    public function isCash(): bool
    {
        return $this->code === 'cash';
    }

    // Helper: cek apakah pakai midtrans
    public function usesMidtrans(): bool
    {
        return !is_null($this->midtrans_payment_type);
    }
}
