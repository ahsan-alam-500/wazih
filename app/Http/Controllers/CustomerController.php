<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = User::where('role', 'customer')->orderBy('id','desc')->get();
        return Inertia::render('customer/Index', [
            'customers' => $customers,
        ]);
    }
}
