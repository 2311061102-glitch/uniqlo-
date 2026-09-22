<?php

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [CatalogController::class, 'products']);
Route::get('/products/{product}', [CatalogController::class, 'product']);
Route::get('/products/{product}/reviews', [ReviewController::class, 'index']);
Route::get('/products/{product}/stock', [CatalogController::class, 'stock']);
Route::get('/categories', [CatalogController::class, 'categories']);

Route::middleware('auth')->group(function () {
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::post('/wishlist/{product}', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{product}', [WishlistController::class, 'destroy']);
});
