<?php

namespace App\Http\Controllers;

use App\Models\EmployeeRange;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeJudge extends Controller
{
    public function index()
    {
        $employeeRanges = EmployeeRange::orderBy("created_at", "desc")->paginate(10);
        return Inertia::render("employees/Judge", [
            "employeeRanges" => $employeeRanges
        ]);
    }
}
