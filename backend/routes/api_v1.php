<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 Routes
|--------------------------------------------------------------------------
|
| All routes here are automatically prefixed with /api/v1/
| by the group defined in routes/api.php.
|
| Middleware groups:
|   Public         — no auth required (register, login, refresh)
|   auth:api       — requires valid JWT access token
|   role:admin     — requires auth + admin role
|
*/

// ------------------------------------------------------------------
// Auth — Public routes (no token required)
// ------------------------------------------------------------------
Route::prefix('auth')->name('auth.')->group(function () {

    // POST /api/v1/auth/register
    Route::post('/register', [AuthController::class, 'register'])->name('register');

    // POST /api/v1/auth/login
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    // POST /api/v1/auth/refresh
    // Uses HttpOnly cookie — no Authorization header needed.
    // Throttled separately (more restrictive) to limit brute-force on refresh endpoint.
    Route::post('/refresh', [AuthController::class, 'refresh'])
        ->middleware('throttle:10,1')
        ->name('refresh');

});

// ------------------------------------------------------------------
// Auth — Protected routes (valid JWT access token required)
// ------------------------------------------------------------------
Route::prefix('auth')->name('auth.')->middleware('auth:api')->group(function () {

    // POST /api/v1/auth/logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // GET /api/v1/auth/me
    Route::get('/me', [AuthController::class, 'me'])->name('me');

});

// ------------------------------------------------------------------
// User routes — future (Step: User Profile)
// ------------------------------------------------------------------
// Route::middleware('auth:api')->prefix('user')->name('user.')->group(function () {
//     Route::get('/profile',  [UserController::class, 'show'])->name('profile.show');
//     Route::put('/profile',  [UserController::class, 'update'])->name('profile.update');
// });

// ------------------------------------------------------------------
// Admin routes — future (Step: Admin Dashboard)
// ------------------------------------------------------------------
// Route::middleware(['auth:api', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
//     ...
// ------------------------------------------------------------------
// Auth — Protected routes (valid JWT access token required)
// ------------------------------------------------------------------
Route::prefix('auth')->name('auth.')->middleware('auth:api')->group(function () {

    // POST /api/v1/auth/logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // GET /api/v1/auth/me
    Route::get('/me', [AuthController::class, 'me'])->name('me');

});

// ------------------------------------------------------------------
// User routes — future (Step: User Profile)
// ------------------------------------------------------------------
// Route::middleware('auth:api')->prefix('user')->name('user.')->group(function () {
//     Route::get('/profile',  [UserController::class, 'show'])->name('profile.show');
//     Route::put('/profile',  [UserController::class, 'update'])->name('profile.update');
// });

// ------------------------------------------------------------------
// Admin routes — future (Step: Admin Dashboard)
// ------------------------------------------------------------------
// Route::middleware(['auth:api', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
//     ...
// });
