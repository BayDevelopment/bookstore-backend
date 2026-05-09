<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentModel extends Model
{
    protected $table = 'payment_methods';

    protected $fillable = [
        'name',
        'code',
        'account_number',
        'account_name',
        'bank_name',
        'description',
        'midtrans_payment_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relasi ────────────────────────────────────────────────
    public function orders()
    {
        return $this->hasMany(OrderModel::class, 'payment_method_id');
    }

    // ── Scope: hanya yang aktif ───────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Helper: apakah pakai Midtrans ─────────────────────────
    public function usesMidtrans(): bool
    {
        return !is_null($this->midtrans_payment_type);
    }

    public function isCash(): bool
    {
        return $this->code === 'cash';
    }
}
