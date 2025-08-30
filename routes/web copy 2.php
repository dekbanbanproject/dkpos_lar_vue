<?php
use Illuminate\Support\Facades\Route;

// ✅ ใช้คลาสจริง ๆ ให้ชัวร์
use App\Http\Middleware\RoleMiddleware;
// use App\Http\Controllers\Api\ProductController;
// use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Api\OrderController   as ApiOrderController;
use App\Http\Controllers\Web\PosController;
use App\Http\Controllers\Auth\StaffAuthController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Admin\ProductsController;
use App\Http\Controllers\Admin\StockInController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\ReportController;

use App\Http\Controllers\Web\ShopController;
use App\Http\Controllers\Shop\ShopApiController;

// หน้าแรกเป็นร้านค้า
Route::get('/', [ShopController::class, 'index'])->name('shop.home');

// API สำหรับร้าน (สาธารณะ)
Route::prefix('shop/api')->group(function () {
    Route::get('/categories', [ShopApiController::class, 'categories']);
    Route::get('/products',   [ShopApiController::class, 'products']);
});

/**
 * PROTECTED — ต้องล็อกอิน (พนักงาน)
 */
Route::middleware('auth:web')->group(function () {

        // POS สำหรับพนักงาน ย้ายไป /pos (ต้องล็อกอิน)
        // Route::middleware('auth:web')->get('/pos', [\App\Http\Controllers\Web\PosController::class, 'index'])->name('pos');
        
        // POS แยกไว้ที่ /pos (ไม่ใช้ /)
        Route::get('/pos', [PosController::class, 'index'])->name('pos');
        
        //  Route::middleware('auth:web')->group(function () {
        //     Route::get('/', [PosController::class, 'index'])->name('pos');
        // });

        // ---------- (พนักงานล็อกอิน/ออก) ----------
        Route::get('/login', [StaffAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [StaffAuthController::class, 'login']);
        Route::post('/logout', [StaffAuthController::class, 'logout'])->name('logout');

        // ---------- (หน้า POS ต้องล็อกอินพนักงาน) ----------
        // Route::middleware('auth:web')->group(function () {
        //     Route::get('/', [PosController::class, 'index'])->name('pos');
        //     // Route::get('/', [PosController::class, 'index'])->name('pos');
        // });



        // Customer auth
        Route::get('/customer/login', [CustomerAuthController::class, 'showLogin'])->name('customer.login');
        Route::post('/customer/login', [CustomerAuthController::class, 'login']);
        Route::post('/customer/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');


        // API สำหรับหน้า POS (ต้องเป็นพนักงานที่ล็อกอิน)
        // Route::middleware(['auth:web'])->prefix('api')->group(function () {
        //     Route::get('/products', [ProductController::class, 'index']);
        //     Route::post('/orders',   [OrderController::class, 'store']);
        // });
        // --- API สำหรับ POS (อยู่ใน web.php เพื่อให้ใช้ session ได้) ---
        Route::middleware(['auth:web'])->prefix('api')->group(function () {
            Route::get('/products', [ApiProductController::class, 'index']); // ← ต้องมีบรรทัดนี้
            Route::post('/orders',   [ApiOrderController::class, 'store']);
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
         // ลูกค้าเข้าหน้าโปรไฟล์/ประวัติคำสั่งซื้อ
        Route::middleware('auth:customer')->group(function () {
            Route::view('/customer', 'customer.dashboard')->name('customer.home');
        });
        Route::middleware(['auth:web', RoleMiddleware::class.':admin,manager'])
            ->prefix('admin')->name('admin.')->group(function () {
                Route::resource('products', ProductsController::class)->except(['show']);
                Route::resource('stock-ins', StockInController::class)->only(['index','create','store','show']);
                Route::resource('expenses', ExpenseController::class)->only(['index','create','store']);
                Route::get('reports/cashflow', [ReportController::class, 'cashflow'])->name('reports.cashflow');
                Route::get('reports/cashflow.csv', [ReportController::class, 'cashflowCsv'])->name('reports.cashflow.csv');
        });

        // Route::middleware(['auth:web', \App\Http\Middleware\RoleMiddleware::class . ':admin,manager'])
        //     ->prefix('admin')->name('admin.')->group(function () {
        //         Route::resource('expenses', ExpenseController::class)->only(['index','create','store']);
        //         Route::get('reports/cashflow', [ReportController::class, 'cashflow'])->name('reports.cashflow');
        //         Route::get('reports/cashflow.csv', [ReportController::class, 'cashflowCsv'])->name('reports.cashflow.csv');
        //     });

       
});