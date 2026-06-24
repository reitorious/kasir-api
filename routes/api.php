<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CategoryController;

// Route Login (Publik)
Route::post('/login', [AuthController::class, 'login']);

// Route yang wajib Login dulu (Proteksi Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // API yang bisa diakses SEMUA (Karyawan & Admin)
    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/transactions', [TransactionController::class, 'store']);
    Route::get('/transactions', [TransactionController::class, 'index']);

    // API KHUSUS ADMIN (Dilindungi Middleware 'admin')
    Route::middleware('admin')->group(function () {
        // Kita gunakan apiResource agar Laravel otomatis membuatkan rute CRUD lengkap
        Route::apiResource('/admin/categories', CategoryController::class);
        Route::apiResource('/admin/products', ProductController::class)->except(['index']); 
    });
});