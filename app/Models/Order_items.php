<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order_items extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
        'total',
    ];

    public function order()
    {
        return $this->belongsTo('App\Models\Order');
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
