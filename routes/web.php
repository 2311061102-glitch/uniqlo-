<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\Admin\VoucherController as AdminVoucherController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\Admin\VoucherController as AdminVoucherController;
use App\Http\Controllers\SepayWebhookController;

/*
|--------------------------------------------------------------------------
| Trang chủ tạm thời (sẽ thay bằng trang chủ thật ở Giai đoạn 6)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
})->name('home');



// Khách hàng xem danh sách voucher đang có hiệu lực (không bắt buộc đăng nhập)
Route::get('/ma-giam-gia', [VoucherController::class, 'index'])->name('vouchers.index');
 
 
// Admin quản lý voucher (quyền admin được tự kiểm tra bên trong Controller)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/vouchers', [AdminVoucherController::class, 'index'])->name('vouchers.index');
    Route::get('/vouchers/create', [AdminVoucherController::class, 'create'])->name('vouchers.create');
    Route::post('/vouchers', [AdminVoucherController::class, 'store'])->name('vouchers.store');
    Route::get('/vouchers/{voucher}/edit', [AdminVoucherController::class, 'edit'])->name('vouchers.edit');
    Route::put('/vouchers/{voucher}', [AdminVoucherController::class, 'update'])->name('vouchers.update');
    Route::delete('/vouchers/{voucher}', [AdminVoucherController::class, 'destroy'])->name('vouchers.destroy');
    Route::patch('/vouchers/{voucher}/toggle-active', [AdminVoucherController::class, 'toggleActive'])->name('vouchers.toggle-active');
});
/*
|--------------------------------------------------------------------------
| Route công khai — sản phẩm & danh mục, ai cũng xem được
|--------------------------------------------------------------------------
*/
Route::get('/san-pham', [ProductController::class, 'index'])->name('products.index');
Route::get('/san-pham/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/san-pham/{product:slug}/kiem-tra-ton-kho', [ProductController::class, 'checkStock'])->name('products.checkStock');

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
    Route::post('/dang-ky', [AuthController::class, 'register'])->name('register.store');

    Route::get('/dang-nhap', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/dang-nhap', [AuthController::class, 'login'])->name('login.store');
    Route::get('/dang-nhap/google', [GoogleController::class, 'redirect'])->name('login.google');
    Route::get('/dang-nhap/google/callback', [GoogleController::class, 'callback'])->name('login.google.callback');

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
| Route phụ trách: Giỏ hàng - Checkout - Đơn hàng - Voucher - Thanh toán
| Dán đoạn này vào routes/web.php của nhóm, đặt cạnh các khối route khác.
|--------------------------------------------------------------------------
*/

    Route::get('/thanh-toan', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/thanh-toan/voucher', [CheckoutController::class, 'applyVoucher'])->name('checkout.voucher.apply');
    Route::delete('/thanh-toan/voucher', [CheckoutController::class, 'removeVoucher'])->name('checkout.voucher.remove');
    Route::post('/thanh-toan/khu-vuc', [CheckoutController::class, 'updateRegion'])->name('checkout.region');
    Route::post('/thanh-toan/dia-chi', [CheckoutController::class, 'updateAddress'])->name('checkout.address');
    Route::post('/thanh-toan', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::post('/thanh-toan/voucher', [CheckoutController::class, 'applyVoucher'])->name('checkout.voucher.apply');
    Route::delete('/thanh-toan/voucher', [CheckoutController::class, 'removeVoucher'])->name('checkout.voucher.remove');

    Route::get('/don-hang-cua-toi', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/don-hang-cua-toi/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/don-hang-cua-toi/{order}/huy', [OrderController::class, 'cancel'])->name('orders.cancel');


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
