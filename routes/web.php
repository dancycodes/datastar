<?php

use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/tasks/get-form/{task}', [TaskController::class, 'getForm'])
    ->name('tasks.getForm')
    ->middleware('verified');

Route::get('/tasks/get-item/{task}', [TaskController::class, 'getItem'])
    ->name('tasks.getItem')
    ->middleware('verified');

Route::patch('/tasks/{task}', [TaskController::class, 'update'])
    ->name('tasks.update')
    ->middleware('verified');
