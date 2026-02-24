<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['app' => 'Winning Products Intelligence', 'docs' => '/api/winning']);
});
