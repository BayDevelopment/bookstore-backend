<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookModel extends Model
{
    protected $table = 'books';
    protected $fillable = [
        'title',
        'author',
        'category_id',
        'fakultas_id',
        'cover',
        'views',
        'description',
        'has_print',
        'price_print',
        'stock',
        'has_pdf',
        'price_pdf',
        'file_path',
    ];

    protected $casts = [
        'has_print'   => 'boolean',
        'has_pdf'     => 'boolean',
        'price_print' => 'decimal:2',
        'price_pdf'   => 'decimal:2',
        'stock'       => 'integer',
    ];

    // Relasi
    public function category()
    {
        return $this->belongsTo(CategoriesModel::class, 'category_id');
    }

    public function fakultas()
    {
        return $this->belongsTo(FakultasModel::class, 'fakultas_id');
    }

    // Helper: harga berdasarkan tipe yang dipilih user
    public function priceFor(string $type): ?float
    {
        return match ($type) {
            'print' => $this->has_print ? (float) $this->price_print : null,
            'pdf'   => $this->has_pdf   ? (float) $this->price_pdf   : null,
            default => null,
        };
    }

    // Helper: apakah tersedia untuk dibeli
    public function availableFor(string $type): bool
    {
        return match ($type) {
            'print' => $this->has_print && $this->stock > 0,
            'pdf'   => $this->has_pdf,
            default => false,
        };
    }
}
