<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderModel extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'user_id',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
