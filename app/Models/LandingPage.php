<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandingPage extends Model
{
    protected $table = "landing_pages";

    protected $casts = [
        'images' => 'array',
    ];
    protected $fillable = [
        "title",
        "description",
        "banner",
        "images",
        "product_id"
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
