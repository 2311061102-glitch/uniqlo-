<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
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

// ===== GIỎ HÀNG: không yêu cầu đăng nhập =====
Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');
Route::post('/gio-hang/them/{variant}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/gio-hang/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/gio-hang/{item}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/gio-hang', [CartController::class, 'clear'])->name('cart.clear');
Route::post('/gio-hang/chon-thanh-toan', [CartController::class, 'selectForCheckout'])->name('cart.select-for-checkout');

Route::post('/webhooks/sepay', [SepayWebhookController::class, 'handle'])->name('webhooks.sepay');

// ===== CHECKOUT + ĐƠN HÀNG: yêu cầu đăng nhập =====
Route::middleware('auth')->group(function () {

    Route::get('/thanh-toan', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/thanh-toan/voucher', [CheckoutController::class, 'applyVoucher'])->name('checkout.voucher.apply');
    Route::delete('/thanh-toan/voucher', [CheckoutController::class, 'removeVoucher'])->name('checkout.voucher.remove');
    Route::post('/thanh-toan/khu-vuc', [CheckoutController::class, 'updateRegion'])->name('checkout.region');
    Route::post('/thanh-toan/dia-chi', [CheckoutController::class, 'updateAddress'])->name('checkout.address');
    Route::post('/thanh-toan', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/thanh-toan/{order}/qr', [CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::post('/thanh-toan/{order}/xac-nhan', [CheckoutController::class, 'confirmPayment'])->name('checkout.payment.confirm');
    Route::get('/thanh-toan/{order}/thanh-cong', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/thanh-toan/{order}/trang-thai', [CheckoutController::class, 'checkPaymentStatus'])->name('checkout.status');
    Route::post('/thanh-toan/{order}/gia-lap-sepay', [SepayWebhookController::class, 'simulate'])->name('sepay.simulate');

    Route::get('/don-hang-cua-toi', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/don-hang-cua-toi/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/don-hang-cua-toi/{order}/huy', [OrderController::class, 'cancel'])->name('orders.cancel');


});
