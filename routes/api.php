<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RefreshController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductVariantController;
use App\Http\Controllers\Api\V1\InventoryController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\ProductImportController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {

    // Public
    Route::post('/auth/register', [RegisterController::class, 'register']);
    Route::post('/auth/login',    [LoginController::class, 'login']);

    // Protected
    Route::middleware('auth:api')->group(function () {
        Route::post('/auth/refresh', [RefreshController::class, 'refresh']);
        Route::post('/auth/logout',  [LogoutController::class, 'logout']);
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/products/{product}', [ProductController::class, 'show']);
        Route::get('/products/search', [ProductController::class, 'search']);
        Route::get('/inventories/{variant}', [InventoryController::class, 'show']);
        Route::get('/products/{product}/variants', [ProductVariantController::class, 'index']);
        Route::get('/variants/{variant}', [ProductVariantController::class, 'show']);

        Route::post('/orders', [OrderController::class, 'store']);                    // create pending
        Route::post('/orders/{order}/confirm', [OrderController::class, 'confirm']); // confirm & deduct stock
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);   // cancel & restore
        Route::post('/orders/{order}/status', [OrderController::class, 'changeStatus']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);
    });

    Route::middleware(['auth:api','role:admin|vendor'])->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);

        // Variant endpoints (optional)
        Route::post('/products/{product}/variants', [ProductVariantController::class, 'store']);
        Route::put('/variants/{variant}', [ProductVariantController::class, 'update']);
        Route::delete('/variants/{variant}', [ProductVariantController::class, 'destroy']);

        Route::post('/inventories/{variant}/adjust', [InventoryController::class, 'adjust']);

        // CSV import endpoint
        Route::post('/products/import', [ProductImportController::class, 'upload']);
        Route::get('/products/import/{import}/status', [ProductImportController::class, 'status']);
    });
});
