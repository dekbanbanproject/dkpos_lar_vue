<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Middleware\RoleMiddleware;

use App\Http\Controllers\Web\ShopController;
use App\Http\Controllers\Shop\ShopApiController;

use App\Http\Controllers\Web\PosController;

use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Api\OrderController   as ApiOrderController;

use App\Http\Controllers\Auth\StaffAuthController;
use App\Http\Controllers\Auth\CustomerAuthController;

use App\Http\Controllers\Admin\ProductsController;
use App\Http\Controllers\Admin\StockInController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Web\CheckoutController;

// ไปชำระเงินด้วย PromptPay / โอน
Route::get('/checkout', [CheckoutController::class, 'show'])->name('shop.checkout.show');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('shop.checkout.store');
Route::get('/checkout/pay/{order}', [CheckoutController::class, 'pay'])->name('shop.checkout.pay');
Route::post('/checkout/{order}/slip', [CheckoutController::class, 'uploadSlip'])->name('shop.checkout.slip');
Route::get('/checkout/done/{order}', [CheckoutController::class, 'done'])->name('shop.checkout.done');

// Poll สถานะออเดอร์
Route::get('/shop/api/orders/{order}/status', [CheckoutController::class, 'status']);


/* =========================================================
 |  PUBLIC (ไม่ต้องล็อกอิน) — หน้า “ร้าน”
 * =======================================================*/
Route::get('/', [ShopController::class, 'index'])->name('shop.home');

Route::prefix('shop/api')->group(function () {
    Route::get('/categories', [ShopApiController::class, 'categories']);
    Route::get('/products',   [ShopApiController::class, 'products']);
});

/* =========================================================
 |  AUTH ROUTES — พนักงาน (web guard)
 *  - login/logout ต้องอยู่นอกกลุ่ม auth เพื่อไม่ให้เด้ง loop
 * =======================================================*/
Route::middleware('guest:web')->group(function () {
    Route::get('/login',  [StaffAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [StaffAuthController::class, 'login']);
});
Route::post('/logout', [StaffAuthController::class, 'logout'])
    ->middleware('auth:web')
    ->name('logout');

/* =========================================================
 |  AUTH ROUTES — ลูกค้า (customer guard)
 * =======================================================*/
Route::middleware('guest:customer')->group(function () {
    Route::get('/customer/login',  [CustomerAuthController::class, 'showLogin'])->name('customer.login');
    Route::post('/customer/login', [CustomerAuthController::class, 'login']);
});
Route::post('/customer/logout', [CustomerAuthController::class, 'logout'])
    ->middleware('auth:customer')
    ->name('customer.logout');

Route::middleware('auth:customer')->group(function () {
    Route::view('/customer', 'customer.dashboard')->name('customer.home');
});

/* =========================================================
 |  PROTECTED — ต้องล็อกอินพนักงาน (web guard)
 * =======================================================*/
Route::middleware('auth:web')->group(function () {

    // POS (พนักงานเท่านั้น)
    Route::get('/pos', [PosController::class, 'index'])->name('pos');

    // API สำหรับ POS (อยู่ใน web.php เพื่อใช้ session/CSRF)
    Route::prefix('api')->group(function () {
        Route::get('/products', [ApiProductController::class, 'index']);
        Route::post('/orders',   [ApiOrderController::class, 'store']);
    });

    // หลังบ้าน: admin + manager
    Route::middleware(RoleMiddleware::class . ':admin,manager')
        ->prefix('admin')->name('admin.')->group(function () {
            Route::resource('products',  ProductsController::class)->except(['show']);
            Route::resource('stock-ins', StockInController::class)->only(['index','create','store','show']);
            Route::resource('expenses',  ExpenseController::class)->only(['index','create','store']);
            Route::get('reports/cashflow',     [ReportController::class, 'cashflow'])->name('reports.cashflow');
            Route::get('reports/cashflow.csv', [ReportController::class, 'cashflowCsv'])->name('reports.cashflow.csv');

            Route::get('orders/pending', [\App\Http\Controllers\Admin\OrderApproveController::class, 'index'])->name('orders.pending');
            Route::post('orders/{order}/approve', [\App\Http\Controllers\Admin\OrderApproveController::class, 'approve'])->name('orders.approve');

            Route::get('orders/{order}/edit', [\App\Http\Controllers\Admin\OrderEditController::class,'edit'])->name('orders.edit');
            Route::put('orders/{order}', [\App\Http\Controllers\Admin\OrderEditController::class,'update'])->name('orders.update');

        });

    // เฉพาะ admin ล้วน
    Route::middleware(RoleMiddleware::class . ':admin')
        ->prefix('admin')->group(function () {
            Route::view('users', 'admin.users')->name('admin.users');
        });
});
