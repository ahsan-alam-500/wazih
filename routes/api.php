<?php

use App\Http\Controllers\client\OrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/v1/order', [OrderController::class, 'create']);
Route::post('/v1/order/update', [OrderController::class,'update']);
