<?php

use App\Models\Task;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.index', [
        'tasks' => Task::all()
    ]);
});


