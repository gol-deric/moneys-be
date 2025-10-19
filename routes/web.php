<?php

use Illuminate\Support\Facades\Route;

// API Root
Route::get('/', function () {
    return response()->json([
        'message' => 'MoneyS API',
        'version' => '2.0.0',
        'documentation' => url('/api/documentation'),
        'admin' => url('/admin'),
    ]);
});

// Filament admin panel handles all /admin routes automatically
// No need to define custom admin routes here
