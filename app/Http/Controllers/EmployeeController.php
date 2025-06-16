<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmployeeController extends Controller
{
    public function index()
    {
        $employee = User::whereIn("role", ["agent", "staff"])
            ->orderBy("id", "desc")->get();
        return Inertia::render("employees/Index", [
            "employees" => $employee
        ]);
    }
}
