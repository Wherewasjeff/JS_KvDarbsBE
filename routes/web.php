<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;

Route::get('/', function () {
    return view('welcome');
});

// Add your registration route
Route::post('/register', [RegisterController::class, 'register']);
