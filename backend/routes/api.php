<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Entry Point
|--------------------------------------------------------------------------
|
| This file is the entry point for all API routes.
| Version groups are split into separate files for clarity and scalability.
| Adding a v2 in the future means creating routes/api_v2.php and adding
| a new prefix group here — without touching any v1 routes.
|
*/

// Health check — not versioned intentionally (used by uptime monitors & Docker)
Route::get('/ping', fn () => response()->json([
    'success' => true,
    'message' => 'API is running',
]));

// API v1 — all application routes
Route::prefix('v1')->group(base_path('routes/api_v1.php'));
