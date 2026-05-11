<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OrderModel extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'user_id',
        'payment_method_id',
        'quantity',
        'total',
        'status',
        'payment_proof',
        'proof_status',
        'proof_note',
    ];

    protected $casts = [
        'total' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(OrderItemModel::class, 'order_id');
    }

    // public function book()
    // {
    //     return $this->belongsTo(BookModel::class, 'book_id');
    // }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function book()
    {
        return $this->belongsTo(BookModel::class, 'book_id');
    }

    protected static function booted()
    {
        static::deleting(function ($model) {
            if ($model->payment_proof && Storage::disk('public')->exists($model->payment_proof)) {
                Storage::disk('public')->delete($model->payment_proof);
            }
        });
    }
}
