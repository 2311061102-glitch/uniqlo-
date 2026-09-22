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
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ManagementController;
use App\Http\Controllers\SepayWebhookController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\WishlistController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Giữ trang chủ render được trong lúc database chưa migrate (ví dụ health check/test sạch).
    $featuredProducts = collect();
    $homeCategories = collect();
    if (\Illuminate\Support\Facades\Schema::hasTable('products')) {
        $featuredProducts = \App\Models\Product::active()->with(['images', 'variants', 'reviews'])->orderByDesc('is_featured')->orderByDesc('sold_count')->take(4)->get();
    }
    if (\Illuminate\Support\Facades\Schema::hasTable('categories')) {
        $homeCategories = \App\Models\Category::where('is_active', true)->whereNull('parent_id')->withCount('products')->orderBy('name')->take(8)->get();
    }
    return view('welcome', compact('featuredProducts', 'homeCategories'));
})->name('home');

Route::get('/san-pham', [ProductController::class, 'index'])->name('products.index');
Route::get('/tim-kiem/goi-y', [SearchController::class, 'suggestions'])->name('search.suggestions');
Route::get('/san-pham/{product:slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/san-pham/{product:slug}/kiem-tra-ton-kho', [ProductController::class, 'checkStock'])->name('products.checkStock');
Route::get('/san-pham/{product:slug}/danh-gia', [ReviewController::class, 'indexJson'])->name('reviews.indexJson');

Route::get('/danh-muc', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/danh-muc/{category}', [ProductController::class, 'byCategory'])->name('products.category');
Route::get('/he-thong-cua-hang', [StoreController::class, 'index'])->name('stores.index');

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
Route::post('/webhooks/sepay', [SepayWebhookController::class, 'handle'])->name('payments.sepay.webhook');

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
    Route::get('/don-hang/{order}/huy', [OrderController::class, 'cancelConfirm'])->name('orders.cancel.confirm');
    Route::post('/don-hang/{order}/huy', [OrderController::class, 'cancel'])->name('orders.cancel');

    Route::get('/yeu-thich', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/yeu-thich/{product:slug}', [WishlistController::class, 'toggle'])->name('wishlist.add');
    Route::delete('/yeu-thich/{product:slug}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

    Route::get('/don-hang/{order}/thanh-toan-vietqr', [PaymentController::class, 'vietqr'])->name('payments.vietqr');
    Route::get('/don-hang/{order}/thanh-toan-vnpay', [PaymentController::class, 'vnpay'])->name('payments.vnpay.pay');
    Route::get('/don-hang/{order}/thanh-toan/status', [PaymentController::class, 'status'])->name('checkout.status');
    Route::post('/don-hang/{order}/sepay-demo', [SepayWebhookController::class, 'simulate'])->name('sepay.simulate');

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

Route::prefix('quan-tri')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::view('/phan-quyen', 'admin.permissions')->name('permissions');
    Route::get('/san-pham', [ManagementController::class, 'products'])->name('products.index');
    Route::get('/san-pham/tao-moi', [ManagementController::class, 'productCreate'])->name('products.create');
    Route::post('/san-pham', [ManagementController::class, 'productStore'])->name('products.store');
    Route::get('/san-pham/{product}/sua', [ManagementController::class, 'productEdit'])->name('products.edit');
    Route::put('/san-pham/{product}', [ManagementController::class, 'productUpdate'])->name('products.update');
    Route::patch('/san-pham/{product}/bat-tat', [ManagementController::class, 'productToggle'])->name('products.toggle');
    Route::delete('/san-pham/{product}', [ManagementController::class, 'productDestroy'])->name('products.destroy');
    Route::get('/danh-muc', [ManagementController::class, 'categories'])->name('categories.index');
    Route::get('/danh-muc/tao-moi', [ManagementController::class, 'categoryCreate'])->name('categories.create');
    Route::post('/danh-muc', [ManagementController::class, 'categoryStore'])->name('categories.store');
    Route::get('/danh-muc/{category}/sua', [ManagementController::class, 'categoryEdit'])->name('categories.edit');
    Route::put('/danh-muc/{category}', [ManagementController::class, 'categoryUpdate'])->name('categories.update');
    Route::patch('/danh-muc/{category}/bat-tat', [ManagementController::class, 'categoryToggle'])->name('categories.toggle');
    Route::delete('/danh-muc/{category}', [ManagementController::class, 'categoryDestroy'])->name('categories.destroy');
    Route::get('/don-hang', [ManagementController::class, 'orders'])->name('orders.index');
    Route::get('/don-hang/{order}', [ManagementController::class, 'orderShow'])->name('orders.show');
    Route::patch('/don-hang/{order}', [ManagementController::class, 'orderStatus'])->name('orders.update');
    Route::get('/khach-hang', [ManagementController::class, 'customers'])->name('customers.index');
});

Route::prefix('quan-tri/vouchers')->name('admin.vouchers.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminVoucherController::class, 'index'])->name('index');
    Route::get('/tao-moi', [AdminVoucherController::class, 'create'])->name('create');
    Route::post('/', [AdminVoucherController::class, 'store'])->name('store');
    Route::get('/{voucher}/sua', [AdminVoucherController::class, 'edit'])->name('edit');
    Route::put('/{voucher}', [AdminVoucherController::class, 'update'])->name('update');
    Route::patch('/{voucher}/bat-tat', [AdminVoucherController::class, 'toggleActive'])->name('toggle-active');
    Route::delete('/{voucher}', [AdminVoucherController::class, 'destroy'])->name('destroy');
});
