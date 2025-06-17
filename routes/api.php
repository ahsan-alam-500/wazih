<?php

use App\Http\Controllers\AbandonedController;
use App\Http\Controllers\client\OrderController;
use App\Http\Controllers\FraudCheckerController;
use App\Http\Controllers\LandingPageController;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
//products
Route::get('/v1/products', function () {
    $products = Product::latest()->take(20)->get(); // optional limit
    return response()->json(['products' => $products]);
});
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
//abandoned cart to order
Route::post('/v1/abandoned/to/order', [AbandonedController::class, 'update']);

Route::delete('/admin/landingPages/image/{id}/{index}', [LandingPageController::class, 'removeImage']);
