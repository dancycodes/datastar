<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/auth/register', [AuthController::class, 'register'])->name('register.post');
