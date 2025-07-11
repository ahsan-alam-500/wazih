<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'order_id',
        'payment_method',
        'amount',
        'status',
        'paid_at',
    ];

    public function order()
    {
        return $this->belongsTo('App\Models\Order');
    }
}
