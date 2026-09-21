<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'UNIQLO Men - Đồ án')</title>

    <link rel="stylesheet" href="{{ asset('css/uniqlo-full-combined.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vouchers.css') }}">
    <link rel="stylesheet" href="{{ asset('css/address-map.css') }}">
</head>
<body>

    <header class="site-header">
        <a href="{{ route('home') }}" class="site-header__logo">UNIQLO</a>
        <nav class="site-header__nav">
            <a href="{{ route('products.index') }}">Sản phẩm</a>
            <a href="{{ route('categories.index') }}">Danh mục</a>

            @guest
                <a href="{{ route('cart.index') }}" class="site-header__action" aria-label="Cart" title="Cart">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 1.9-1.4L21 8H6"/></svg>
                    @if ($cartCount = \App\Services\CartCounter::count())
                        <span class="site-header__badge">{{ $cartCount }}</span>
                    @endif
                </a>
            @endguest

            @auth
                <a href="{{ route('cart.index') }}" class="site-header__action" aria-label="Giỏ hàng" title="Giỏ hàng">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.8a2 2 0 0 0 1.9-1.4L21 8H6M10 20a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm9 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0Z"/></svg>
                    @if ($cartCount = \App\Services\CartCounter::count())
                        <span class="site-header__badge">{{ $cartCount }}</span>
                    @endif
                </a>
                <a href="{{ route('profile.edit') }}" class="site-header__action" aria-label="Tài khoản" title="Tài khoản">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.5"/><path d="M4.5 20a7.5 7.5 0 0 1 15 0"/></svg>
                </a>
                <form method="POST" action="{{ route('logout') }}" style="display:inline">
                    @csrf
                    <button type="submit" class="link-button">Đăng xuất</button>
                </form>
            @else
                <a href="{{ route('login') }}">Đăng nhập</a>
                <a href="{{ route('register') }}">Đăng ký</a>
            @endauth
        </nav>
    </header>

    <main class="site-main">
        @if (session('success'))
            <div class="alert alert--success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert--error">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
