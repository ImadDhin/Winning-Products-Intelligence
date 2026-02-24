<?php

use App\Http\Controllers\Api\WinningController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WatchlistController;
use App\Http\Controllers\Api\AlertController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::get('/auth/google', [AuthController::class, 'googleRedirect'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'googleCallback'])->name('auth.google.callback');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/winning', [WinningController::class, 'index'])->middleware('throttle:60,1');
    Route::get('/products/{id}', [ProductController::class, 'show'])->middleware('throttle:60,1');

    Route::get('/watchlist', [WatchlistController::class, 'index']);
    Route::post('/watchlist', [WatchlistController::class, 'store']);
    Route::delete('/watchlist/{product_id}', [WatchlistController::class, 'destroy']);

    Route::get('/alerts', [AlertController::class, 'index']);
    Route::post('/alerts', [AlertController::class, 'store']);
    Route::patch('/alerts/{id}', [AlertController::class, 'update']);
    Route::delete('/alerts/{id}', [AlertController::class, 'destroy']);
});

Route::get('/health', function () {
    $db = false;
    $redis = false;
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $db = true;
    } catch (\Throwable $e) {
        //
    }
    try {
        \Illuminate\Support\Facades\Redis::ping();
        $redis = true;
    } catch (\Throwable $e) {
        //
    }
    return response()->json(['db' => $db, 'redis' => $redis], $db && $redis ? 200 : 503);
});
