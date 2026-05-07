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
        'type',
        'price',
        'cover',
        'file_path',
        'stock',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(CategoriesModel::class);
    }

    public function fakultas()
    {
        return $this->belongsTo(FakultasModel::class);
    }
    public function prodi()
    {
        return $this->belongsTo(ProdiModel::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItemModel::class, 'book_id');
    }
}
