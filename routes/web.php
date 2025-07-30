<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/tasks/get-form/{task}', [TaskController::class, 'getForm'])
    ->name('tasks.getForm');
// ->middleware('verified');

Route::post('/tasks/get-item/{task}', [TaskController::class, 'getItem'])
    ->name('tasks.getItem')
    ->middleware('verified');