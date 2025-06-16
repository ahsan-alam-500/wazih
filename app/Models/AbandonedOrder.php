<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbandonedOrder extends Model
{
    protected $table = "abandoned_orders";
    protected $fillable = [
        "name",
        "mobile",
        "email",
        "product_name",
        "product_image",
        "product_price",
    ];
}
