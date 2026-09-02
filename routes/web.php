<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Laravel API backend ready',
        'status' => 'ok',
    ]);
});
