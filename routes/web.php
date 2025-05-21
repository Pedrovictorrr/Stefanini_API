<?php

use Illuminate\Support\Facades\Route;


Route::get('/login-redirect', function () {
    return response()->json([
        'message' => 'Por favor, faça login para acessar este recurso.',
    ], 401);
})->name('login');