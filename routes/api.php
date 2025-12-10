<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CaaSController;

// Authenticated API with 120 requests per minute
// TODO: Request amount depends on user group
Route::middleware(['auth:sanctum', 'throttle:120,1'])
    ->get('/{path}', [CaaSController::class, 'serve'])
    ->where('path', '.*');

// Public API with 60 requests per minute
Route::middleware(['throttle:60,1']) // 100 requests per minute
    ->get('/{path}', [CaaSController::class, 'serve'])
    ->where('path', '.*');
