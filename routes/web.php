<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\Admin\VoucherController as AdminVoucherController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/san-pham', [ProductController::class, 'index'])->name('products.index');
Route::get('/san-pham/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/san-pham/{product:slug}/kiem-tra-ton-kho', [ProductController::class, 'checkStock'])->name('products.checkStock');
Route::get('/san-pham/{product:slug}/danh-gia', [ReviewController::class, 'indexJson'])->name('reviews.indexJson');

Route::get('/danh-muc', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/danh-muc/{category:slug}', [ProductController::class, 'byCategory'])->name('products.category');

Route::get('/ma-giam-gia', [VoucherController::class, 'index'])->name('vouchers.index');
Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');
Route::post('/gio-hang', [CartController::class, 'store'])->name('cart.store');
Route::post('/gio-hang/them/{variant}', [CartController::class, 'add'])->name('cart.add');
Route::put('/gio-hang/{cartItem}', [CartController::class, 'update'])->name('cart.update');
Route::patch('/gio-hang/{cartItem}', [CartController::class, 'update'])->name('cart.update.patch');
Route::delete('/gio-hang/{cartItem}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::delete('/gio-hang', [CartController::class, 'clear'])->name('cart.clear');

Route::get('/thanh-toan/vnpay/return', [PaymentController::class, 'vnpayReturn'])->name('payments.vnpay.return');
Route::get('/thanh-toan/vnpay/ipn', [PaymentController::class, 'vnpayIpn'])->name('payments.vnpay.ipn');

Route::middleware('guest')->group(function () {
    Route::get('/dang-ky', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/dang-ky', [AuthController::class, 'register'])
        ->middleware('throttle:5,1')
        ->name('register.store');

    Route::get('/dang-nhap', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->name('login.store');
    Route::get('/dang-nhap/google', [GoogleController::class, 'redirect'])->name('login.google');
    Route::get('/dang-nhap/google/callback', [GoogleController::class, 'callback'])->name('login.google.callback');

    Route::get('/quen-mat-khau', [ForgotPasswordController::class, 'show'])->name('password.request');
    Route::post('/quen-mat-khau', [ForgotPasswordController::class, 'send'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    Route::get('/dat-lai-mat-khau/{token}', [ResetPasswordController::class, 'show'])->name('password.reset');
    Route::post('/dat-lai-mat-khau', [ResetPasswordController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/dang-xuat', [AuthController::class, 'logout'])->name('logout');

    Route::get('/tai-khoan', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/tai-khoan', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/tai-khoan', [ProfileController::class, 'destroy'])->name('profile.destroy');

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

    Route::post('/san-pham/{product:slug}/danh-gia', [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/danh-gia/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');

    Route::get('/thanh-toan', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/thanh-toan', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::post('/thanh-toan/voucher', [CheckoutController::class, 'applyVoucher'])->name('checkout.voucher.apply');
    Route::delete('/thanh-toan/voucher', [CheckoutController::class, 'removeVoucher'])->name('checkout.voucher.remove');

    Route::get('/don-hang', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/don-hang/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/don-hang/{order}/huy', [OrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('/don-hang/{order}/thanh-toan-vietqr', [PaymentController::class, 'vietqr'])->name('payments.vietqr');
    Route::get('/don-hang/{order}/thanh-toan-vnpay', [PaymentController::class, 'vnpay'])->name('payments.vnpay.pay');

    Route::post('/don-hang/{order}/xac-nhan-thanh-toan', [OrderController::class, 'confirmPayment'])
        ->middleware('role:admin')
        ->name('orders.confirmPayment');

    Route::get('/xac-thuc-email', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/xac-thuc-email/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('home')->with('success', 'Xác thực email thành công!');
    })->middleware('signed')->name('verification.verify');

    Route::post('/xac-thuc-email/gui-lai', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Đã gửi lại email xác thực, vui lòng kiểm tra hộp thư.');
    })->middleware('throttle:6,1')->name('verification.send');
});

Route::prefix('quan-tri/vouchers')->name('admin.vouchers.')->middleware('auth')->group(function () {
    Route::get('/', [AdminVoucherController::class, 'index'])->name('index');
    Route::get('/tao-moi', [AdminVoucherController::class, 'create'])->name('create');
    Route::post('/', [AdminVoucherController::class, 'store'])->name('store');
    Route::get('/{voucher}/sua', [AdminVoucherController::class, 'edit'])->name('edit');
    Route::put('/{voucher}', [AdminVoucherController::class, 'update'])->name('update');
    Route::patch('/{voucher}/bat-tat', [AdminVoucherController::class, 'toggleActive'])->name('toggle-active');
    Route::delete('/{voucher}', [AdminVoucherController::class, 'destroy'])->name('destroy');
});
