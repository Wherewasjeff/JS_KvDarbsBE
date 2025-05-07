<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\WorkerController;
Route::any('/hello', function () {
    return ['message' => 'Hello from Laravel!'];
});
// Public Routes
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::get('/categories', [StorageController::class, 'getCategories']);
Route::get('/storage', [StorageController::class, 'getStorage']);
Route::get('/test', function() {
    return response()->json(['message' => 'API is working']);
});
// Routes that require authentication
Route::middleware(['auth:sanctum'])->group(function () {

    // User & Store Routes
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/user/{id}', [UserController::class, 'show']);
    Route::get('/workers/{worker}', [WorkerController::class, 'show']);
    Route::put('/user/{id}', [UserController::class, 'update']);

    Route::post('/store', [StoreController::class, 'store']);
    Route::get('/show/{store_id}', [StoreController::class, 'show']);
    Route::put('/show/{store_id}', [StoreController::class, 'update']);

    // Storage Routes
    Route::post('/storage', [StorageController::class, 'storage']);
    Route::delete('/storage/{id}', [StorageController::class, 'destroy']);
    Route::post('/categories', [StorageController::class, 'addCategory']);
    Route::delete('/categories/{id}', [StorageController::class, 'destroyCategory']);
    Route::put('/storage/{id}', [StorageController::class, 'update'])->middleware('auth:sanctum');
    Route::post('/assign-categories', [StorageController::class, 'assignCategories']);

    // Worker Routes
    Route::get('/workers', [WorkerController::class, 'index']);
    Route::post('/workers', [WorkerController::class, 'store']);
    Route::put('/workers/{worker}', [WorkerController::class, 'update']);
    Route::delete('/workers/{worker}', [WorkerController::class, 'destroy']);

    //Selling Routes
    Route::post('/sell', [\App\Http\Controllers\SaleController::class, 'store']);
    Route::get('/sales', [\App\Http\Controllers\SaleController::class, 'index']);
});
