<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemModel extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'book_id',
        'qty',
        'type',
        'price',
        'download_count'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'qty'   => 'integer',
        'download_count' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }

    public function book()
    {
        return $this->belongsTo(BookModel::class, 'book_id');
    }

    public function subtotal(): float
    {
        return (float) $this->price * $this->qty;
    }

    public function isPrint(): bool
    {
        return $this->type === 'print';
    }

    public function isPdf(): bool
    {
        return $this->type === 'pdf';
    }
}
