<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    Route::get('admin/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/admin/customers', [CustomerController::class, 'index'])->name('customers');
    Route::get('admin/employees', [EmployeeController::class, 'index'])->name('employees');
});
