<?php

use App\Http\Controllers\AbandonedController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderFilterController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    Route::get('/admin/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/admin/customers', [CustomerController::class, 'index'])->name('customers');
    Route::get('/admin/employees', [EmployeeController::class, 'index'])->name('employees');
    Route::get('/admin/reports', [OrderController::class, 'report'])->name('reports');
    Route::get('/admin/abandoned', [AbandonedController::class, 'index'])->name('abandoned');
    Route::get('/admin/orders/{status}', [OrderFilterController::class, 'index'])->name('orders.status');
});
