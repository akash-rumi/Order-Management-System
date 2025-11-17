<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\RefreshController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\ProductVariantController;


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
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    // Protected
    Route::middleware('auth:api')->group(function () {
        Route::post('/auth/refresh', [RefreshController::class, 'refresh']);
        Route::post('/auth/logout',  [LogoutController::class, 'logout']);
    });

    Route::middleware(['auth:api','role:admin|vendor'])->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);

        // Variant endpoints (optional)
        Route::post('/products/{product}/variants', [ProductVariantController::class, 'store']);
        Route::put('/variants/{variant}', [ProductVariantController::class, 'update']);
        Route::delete('/variants/{variant}', [ProductVariantController::class, 'destroy']);
    });
});
