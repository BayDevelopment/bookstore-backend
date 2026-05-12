<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdfAccessTokenModel extends Model
{
    protected $table = 'pdf_access_tokens';
    protected $fillable = [
        'token',
        'user_id',
        'order_id',
        'book_id',
        'expires_at',
        'used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used'       => 'boolean',
    ];

    // ─── Relations ──────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(BookModel::class, 'book_id');
    }
 
    // ─── Helpers ────────────────────────────────────────────

    /**
     * Cek apakah token masih valid (belum expired dan belum dipakai).
     */
    public function isValid(): bool
    {
        return !$this->used && $this->expires_at->isFuture();
    }

    /**
     * Tandai token sebagai sudah dipakai (single-use, opsional).
     * Kalau mau PDF bisa dibuka terus selama 5 menit, hapus/nonaktifkan ini.
     */
    public function markUsed(): void
    {
        $this->update(['used' => true]);
    }
 
    // ─── Scope ──────────────────────────────────────────────

    /**
     * Hapus token kedaluwarsa (panggil dari command/scheduler).
     */
    public static function purgeExpired(): int
    {
        return static::where('expires_at', '<', now())->delete();
    }
}
