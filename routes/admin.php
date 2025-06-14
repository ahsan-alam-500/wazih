<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

Route::get('admin/orders', [OrderController::class, 'index'])->name('orders.index');

});
