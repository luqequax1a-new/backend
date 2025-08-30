<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Health endpoint
Route::get('/admin/v1/health', function () {
    return response()->json([
        'ok' => true,
        'app' => config('app.name'),
        'time' => now()->toISOString()
    ]);
});

// Debug endpoint
Route::post('/admin/v1/debug', function (Request $request) {
    return response()->json([
        'received_data' => $request->all(),
        'content_type' => $request->header('Content-Type'),
        'accept' => $request->header('Accept'),
        'method' => $request->method()
    ]);
});

// Units API routes
Route::prefix('admin/v1')->group(function () {
    Route::apiResource('units', \App\Http\Controllers\Admin\UnitController::class)
         ->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::patch('units/{id}/status', [\App\Http\Controllers\Admin\UnitController::class, 'updateStatus']);
    Route::patch('units/{id}/replace', [\App\Http\Controllers\Admin\UnitController::class, 'replace']);
    
    // Products API routes
    Route::apiResource('products', \App\Http\Controllers\Admin\ProductController::class)
         ->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::patch('products/{product}/status', [\App\Http\Controllers\Admin\ProductController::class, 'updateStatus']);
    Route::post('products/{id}/duplicate', [\App\Http\Controllers\Admin\ProductController::class, 'duplicate']);
    
    // Helper endpoints
    Route::get('brands', [\App\Http\Controllers\Admin\BrandController::class, 'index']);
    Route::get('stores', [\App\Http\Controllers\Admin\StoreController::class, 'index']);
    Route::get('categories', function () {
        $categories = \App\Models\Category::select('id', 'name')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        return response()->json($categories);
    });
    
    // Store bilgisi (stub)
    Route::get('store.json', [\App\Http\Controllers\Admin\StoreController::class, 'show']);
    
    // Brand listesi (stub)
    Route::get('brand.json', [\App\Http\Controllers\Admin\BrandController::class, 'index']);
});

// Categories API endpoint
Route::get('/admin/v1/categories', function () {
    $categories = DB::table('categories')
        ->where('is_active', 1)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();
    
    return response()->json($categories);
});

// Frontend API routes
Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index']);
Route::get('/products/{product}', [\App\Http\Controllers\ProductController::class, 'show']);