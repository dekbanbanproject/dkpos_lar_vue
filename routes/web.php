<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\PosController;
use App\Http\Controllers\Auth\StaffAuthController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Controllers\Admin\ProductsController;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', [PosController::class, 'index'])->name('pos');
// Staff auth
Route::get('/login', [StaffAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [StaffAuthController::class, 'login']);
Route::post('/logout', [StaffAuthController::class, 'logout'])->name('logout');

// Customer auth
Route::get('/customer/login', [CustomerAuthController::class, 'showLogin'])->name('customer.login');
Route::post('/customer/login', [CustomerAuthController::class, 'login']);
Route::post('/customer/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');

// Protected (พนักงานใช้ POS)
Route::middleware('auth:web')->group(function () {
    Route::get('/', [PosController::class, 'index'])->name('pos');
});
// เมนูที่ admin+manager เข้าถึงได้
// Route::middleware(['auth:web','role:admin,manager'])->group(function () {
//     Route::view('/products', 'admin.products')->name('products.index'); // จัดการสินค้า
//     Route::view('/reports', 'reports.index')->name('reports.index');    // รายงาน
// });
Route::middleware(['auth:web', RoleMiddleware::class . ':admin,manager'])->group(function () {
    Route::view('/products', 'admin.products')->name('products.index'); // จัดการสินค้า
    Route::view('/reports', 'reports.index')->name('reports.index');    // รายงาน
});

// เมนูเฉพาะ admin
// Route::middleware(['auth:web','role:admin'])->group(function () {
//     Route::view('/admin/users', 'admin.users')->name('admin.users');   // จัดการผู้ใช้
// });
Route::middleware(['auth:web', RoleMiddleware::class . ':admin'])->group(function () {
    Route::view('/admin/users', 'admin.users')->name('admin.users');
});
Route::middleware(['auth:web', RoleMiddleware::class.':admin,manager'])
    ->prefix('admin')->name('admin.')->group(function () {
        Route::resource('products', ProductsController::class)->except(['show']);
});

// ลูกค้าเข้าหน้าโปรไฟล์/ประวัติคำสั่งซื้อ
Route::middleware('auth:customer')->group(function () {
    Route::view('/customer', 'customer.dashboard')->name('customer.home');
});
