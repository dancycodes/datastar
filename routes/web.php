<?php

use App\Models\Task;
use Illuminate\Support\Facades\Route;

use Spatie\RouteDiscovery\Discovery\Discover;

Discover::controllers()->in(app_path('Http/Controllers'));

Route::get('/', function () {
    return view('pages.index', [
        'tasks' => Task::all()
    ]);
});


