<?php

// ✅ ใช้คลาสจริง ๆ ให้ชัวร์
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Web\PosController;
use App\Http\Controllers\Auth\StaffAuthController;
use App\Http\Controllers\Auth\CustomerAuthController;
// ---------- (พนักงานล็อกอิน/ออก) ----------
Route::get('/login', [StaffAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [StaffAuthController::class, 'login']);
Route::post('/logout', [StaffAuthController::class, 'logout'])->name('logout');

// ---------- (หน้า POS ต้องล็อกอินพนักงาน) ----------
Route::middleware('auth:web')->group(function () {
    Route::get('/', [PosController::class, 'index'])->name('pos');
});

// ---------- (API สำหรับหน้า POS) ----------
Route::middleware(['auth:web'])->prefix('api')->group(function () {
    Route::get('/products', [ProductController::class, 'index']); // ← อันที่ต้องการ
    Route::post('/orders',   [OrderController::class, 'store']);
});

// ---------- (หลังบ้าน — admin/manager เท่านั้น) ----------
Route::middleware(['auth:web', RoleMiddleware::class . ':admin,manager'])
    ->prefix('admin')->name('admin.')->group(function () {
        Route::resource('products', \App\Http\Controllers\Admin\ProductsController::class)->except(['show']);
    });

// ---------- (ลูกค้า ถ้ามี) ----------
Route::get('/customer/login', [CustomerAuthController::class, 'showLogin'])->name('customer.login');
Route::post('/customer/login', [CustomerAuthController::class, 'login']);
Route::post('/customer/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');
Route::middleware('auth:customer')->group(function () {
    Route::view('/customer', 'customer.dashboard')->name('customer.home');
});