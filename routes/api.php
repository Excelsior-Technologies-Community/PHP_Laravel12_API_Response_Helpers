<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;

/*
|--------------------------------------------------------------------------
| Product API Routes
|--------------------------------------------------------------------------
*/

// Product analytics
Route::get(
    'products/analytics',
    [ProductController::class, 'analytics']
);

// Product search, filtering, sorting and pagination
Route::get(
    'products/search',
    [ProductController::class, 'search']
);

// Product activity history
Route::get(
    'products/history',
    [ProductController::class, 'history']
);

// Product activity CSV export
Route::get(
    'products/history/export',
    [ProductController::class, 'exportHistory']
);

// Standard Product CRUD
Route::apiResource(
    'products',
    ProductController::class
);