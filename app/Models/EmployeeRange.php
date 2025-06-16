<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeRange extends Model
{
    protected $table = "employee_ranges";
    protected $fillable = [
        "name",
        "user_id",
        "order_id",
        "type",
        "stage",
        "status",
        "point",
    ];
}
