<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\client\OrderController;
use App\Models\Activity;
use App\Models\LandingPage;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get("/", function () {
    $products = Product::orderBy("id", "desc")->get();
    $landingPages = LandingPage::with('product')->orderBy("id", "desc")->get();
    return Inertia::render('Welcome', [
        'products' => $products,
        'landingPages' => $landingPages
    ]);
});

Route::get('landingPage/{id}/{name}', function ($id, $name) {
    $landingPage = LandingPage::findOrFail($id);
    return Inertia::render('frontend/LandingPage/show', [
        'landingPage' => $landingPage,
        'id' => $id,
        'title' => $name,
    ]);
})->name('landingPage');

Route::get('/admin/login', function () {
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

Route::get('details/{id}/{name}', function ($id, $name) {
    $product = Product::with('category')->find($id);
    return Inertia::render('frontend/ProductDetails', [
        'product' => $product,
        'id' => $id,
        'title' => $name,
    ]);
})->name('product.details');

Route::get('/cart', [CartController::class, 'viewCart'])->name('cart.view');

Route::get('/checkout', function () {
    return Inertia::render('frontend/Checkout');
})->name('checkout');

Route::post('/checkout', [OrderController::class, 'create'])->name('checkout.store');

// ================================ Required Once ================================

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/admin.php';
