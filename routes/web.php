<?php

use Illuminate\Support\Facades\Route;

use App\Models\Product;
use App\Models\ProductActivity;

Route::get('/', function () {
    $totalProducts = Product::count();
    $totalValue = Product::sum('price');
    $avgPrice = Product::avg('price') ?? 0;
    $totalActivities = ProductActivity::count();
    $recentProducts = Product::latest('id')->take(10)->get();
    $recentActivities = ProductActivity::latest('id')->take(10)->get();

    return view('dashboard', compact(
        'totalProducts',
        'totalValue',
        'avgPrice',
        'totalActivities',
        'recentProducts',
        'recentActivities'
    ));
})->name('dashboard');
