<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Trang chủ tạm thời (sẽ thay bằng trang chủ thật ở Giai đoạn 6)
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

/*
|--------------------------------------------------------------------------
| Route công khai — sản phẩm & danh mục, ai cũng xem được
|--------------------------------------------------------------------------
*/
Route::get('/san-pham', [ProductController::class, 'index'])->name('products.index');
Route::get('/san-pham-khuyen-mai', [ProductController::class, 'sale'])->name('products.sale');
Route::get('/goi-y-san-pham', [ProductController::class, 'suggestions'])->name('products.suggestions');
Route::get('/san-pham/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/san-pham/{product:slug}/kiem-tra-ton-kho', [ProductController::class, 'checkStock'])->name('products.checkStock');

Route::get('/danh-muc', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/danh-muc/{category:slug}', [ProductController::class, 'byCategory'])->name('products.category');

/*
|--------------------------------------------------------------------------
| Route cho khách chưa đăng nhập (guest) — phần Thành viên 1
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/dang-ky', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/dang-ky', [AuthController::class, 'register'])->name('register.store');

    Route::get('/dang-nhap', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->name('login.store');

    Route::get('/quen-mat-khau', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/quen-mat-khau', [ForgotPasswordController::class, 'send'])->name('password.email');

    Route::get('/dat-lai-mat-khau/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/dat-lai-mat-khau', [ResetPasswordController::class, 'reset'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| Route cho user đã đăng nhập (auth) — phần Thành viên 1
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::post('/dang-xuat', [AuthController::class, 'logout'])->name('logout');

    Route::get('/yeu-thich', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/yeu-thich/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/san-pham/{product:slug}/danh-gia', [ReviewController::class, 'store'])->name('reviews.store');

    Route::get('/tai-khoan', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/tai-khoan', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/tai-khoan/doi-mat-khau', [ProfileController::class, 'editPassword'])->name('password.edit');
    Route::put('/tai-khoan/doi-mat-khau', [ProfileController::class, 'updatePassword'])->name('password.change');

    Route::prefix('tai-khoan/dia-chi')->name('addresses.')->group(function () {
        Route::get('/', [AddressController::class, 'index'])->name('index');
        Route::get('/them-moi', [AddressController::class, 'create'])->name('create');
        Route::post('/', [AddressController::class, 'store'])->name('store');
        Route::get('/{address}/sua', [AddressController::class, 'edit'])->name('edit');
        Route::put('/{address}', [AddressController::class, 'update'])->name('update');
        Route::delete('/{address}', [AddressController::class, 'destroy'])->name('destroy');
        Route::patch('/{address}/dat-mac-dinh', [AddressController::class, 'setDefault'])->name('setDefault');
    });
});

/*
|--------------------------------------------------------------------------
| VÍ DỤ middleware phân quyền role (tham khảo cho trang admin sau này):
|--------------------------------------------------------------------------
|
| Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
|     Route::get('/dashboard', function () {
|         return 'Trang quản trị - chỉ admin vào được';
|     });
| });
*/
