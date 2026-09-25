<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;

/*
|--------------------------------------------------------------------------
| Product API Routes
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Existing Analytics
|--------------------------------------------------------------------------
*/

Route::get(
    'products/analytics',
    [ProductController::class, 'analytics']
);

/*
|--------------------------------------------------------------------------
| Existing Search
|--------------------------------------------------------------------------
*/

Route::get(
    'products/search',
    [ProductController::class, 'search']
);

/*
|--------------------------------------------------------------------------
| Existing History
|--------------------------------------------------------------------------
*/

Route::get(
    'products/history',
    [ProductController::class, 'history']
);

/*
|--------------------------------------------------------------------------
| Existing History CSV Export
|--------------------------------------------------------------------------
*/

Route::get(
    'products/history/export',
    [ProductController::class, 'exportHistory']
);

/*
|--------------------------------------------------------------------------
| NEW 1 - Bulk Delete
|--------------------------------------------------------------------------
*/

Route::delete(
    'products/bulk-delete',
    [ProductController::class, 'bulkDelete']
);

/*
|--------------------------------------------------------------------------
| NEW 2 - Bulk Price Update
|--------------------------------------------------------------------------
*/

Route::put(
    'products/bulk-price',
    [ProductController::class, 'bulkUpdatePrice']
);

/*
|--------------------------------------------------------------------------
| NEW 4 - Product CSV Export
|--------------------------------------------------------------------------
*/

Route::get(
    'products/export',
    [ProductController::class, 'exportProducts']
);

/*
|--------------------------------------------------------------------------
| NEW 5 - Price Summary
|--------------------------------------------------------------------------
*/

Route::get(
    'products/price-summary',
    [ProductController::class, 'priceSummary']
);

/*
|--------------------------------------------------------------------------
| NEW 6 - Top Expensive Products
|--------------------------------------------------------------------------
*/

Route::get(
    'products/top-expensive',
    [ProductController::class, 'topExpensive']
);

/*
|--------------------------------------------------------------------------
| NEW 7 - Recent Products
|--------------------------------------------------------------------------
*/

Route::get(
    'products/recent',
    [ProductController::class, 'recentProducts']
);

/*
|--------------------------------------------------------------------------
| NEW 8 - Product Suggestions
|--------------------------------------------------------------------------
*/

Route::get(
    'products/suggestions',
    [ProductController::class, 'suggestions']
);

/*
|--------------------------------------------------------------------------
| NEW 9 - Compare Products
|--------------------------------------------------------------------------
*/

Route::get(
    'products/compare',
    [ProductController::class, 'compare']
);

/*
|--------------------------------------------------------------------------
| NEW 3 - Duplicate Product
|--------------------------------------------------------------------------
*/

Route::post(
    'products/{id}/duplicate',
    [ProductController::class, 'duplicate']
);

/*
|--------------------------------------------------------------------------
| Standard Product CRUD
|--------------------------------------------------------------------------
*/

Route::apiResource(
    'products',
    ProductController::class
);