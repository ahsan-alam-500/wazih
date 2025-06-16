<?php

use App\Http\Controllers\AbandonedController;
use App\Http\Controllers\client\OrderController;
use App\Http\Controllers\FraudCheckerController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// creating ordar
Route::post('/v1/order', [OrderController::class, 'create']);
// order Getting from wordpress websites
Route::post('/v1/wp/order', [OrderController::class, 'wpOrder']);
//updating order status
Route::post('/v1/order/update', [OrderController::class, 'update']);
//fraud checking api
Route::post('/v1/fraudcheck/check', [FraudCheckerController::class, 'check']);
//Abandoned cart
Route::post('/v1/abandoned', [AbandonedController::class, 'store']);

