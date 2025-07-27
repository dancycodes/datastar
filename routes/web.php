<?php

Route::view('/test', 'test');

Route::post('/test/login', [\App\Http\Controllers\AuthController::class, 'test']);
