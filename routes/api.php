<?php

use App\Http\Controllers\client\OrderController;
use App\Http\Controllers\FraudCheckerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// creating ordar
Route::post('/v1/order', [OrderController::class, 'create']);
//updating order status
Route::post('/v1/order/update', [OrderController::class, 'update']);
//fraud checking api
Route::post('/v1/fraudcheck/check', [FraudCheckerController::class, 'check']);
