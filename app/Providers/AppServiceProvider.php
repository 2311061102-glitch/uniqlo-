<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
     public function boot(): void
    {
        // Mọi trang dùng layout "layouts.app" đều tự động có biến $cartCount
        // (số lượng sản phẩm trong giỏ hàng), không cần từng Controller tự truyền.
        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            $view->with('cartCount', \App\Services\CartCounter::count());
        });
    }
}
