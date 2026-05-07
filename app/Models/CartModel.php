<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartModel extends Model
{
    protected $table = 'carts';

    protected $fillable = [
        'user_id',
        'book_id',
        'qty',
    ];

    public function book()
    {
        return $this->belongsTo(BookModel::class, 'book_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
