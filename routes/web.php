<?php

use App\Models\Activity;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('auth/Login');
})->name('home');

Route::get('dashboard', function () {
    $orders = Order::with(['user', 'items.product'])->get();
    $customers = User::where('role', 'customer')->get()->unique('mobile')->values();
    $employees = User::where('role', 'agent')->get();
    $activities = Activity::orderBy('created_at', 'desc')->get();
    return Inertia::render('Dashboard', [
        'orders' => $orders,
        'customers' => $customers,
        'employees' => $employees,
        'activities' => $activities
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
