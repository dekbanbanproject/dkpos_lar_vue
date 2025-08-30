<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\PosController;
use App\Http\Controllers\Auth\StaffAuthController;
use App\Http\Controllers\Auth\CustomerAuthController;

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

// ลูกค้าเข้าหน้าโปรไฟล์/ประวัติคำสั่งซื้อ
Route::middleware('auth:customer')->group(function () {
    Route::view('/customer', 'customer.dashboard')->name('customer.home');
});
