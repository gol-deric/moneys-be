<?php

use Illuminate\Support\Facades\Route;

// This is an API-only application
// All routes are defined in routes/api.php
Route::get('/', function () {
    return response()->json([
        'message' => 'MoneyS API',
        'version' => '2.0.0',
        'documentation' => url('/api/documentation'),
    ]);
});
