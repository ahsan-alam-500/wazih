<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = 'activity_logs';
    protected $fillable = ['action', 'user_id', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
