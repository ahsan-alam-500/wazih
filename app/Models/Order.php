<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'order_number',
        'total_amount',
        'shipping_address'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function items()
    {
        return $this->hasMany(Order_items::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
