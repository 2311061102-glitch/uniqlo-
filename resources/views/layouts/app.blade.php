<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'UNIQLO Men - Đồ án')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/uniqlo-full-combined.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
</head>
<body>

    <header class="site-header">
        <a href="{{ route('home') }}" class="site-header__logo">UNIQLO</a>

        <input type="checkbox" id="nav-toggle" class="nav-toggle-checkbox">
        <label for="nav-toggle" class="nav-toggle-button" aria-label="Mở menu">☰</label>

        <nav class="site-header__nav">
            <a href="{{ route('products.index') }}">Sản phẩm</a>
            <a href="{{ route('categories.index') }}">Danh mục</a>

            @auth
                <a href="{{ route('cart.index') }}">Giỏ hàng ({{ auth()->user()->cartItems()->sum('quantity') }})</a>
                <a href="{{ route('orders.index') }}">Đơn hàng của tôi</a>
                <a href="{{ route('addresses.index') }}">Sổ địa chỉ</a>
                <a href="{{ route('profile.edit') }}">Xin chào, {{ auth()->user()->name }}</a>
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

        @auth
            @unless (auth()->user()->hasVerifiedEmail())
                <div class="alert alert--warning">
                    Email của bạn chưa được xác thực.
                    <a href="{{ route('verification.notice') }}">Xem chi tiết</a> hoặc
                    <form method="POST" action="{{ route('verification.send') }}" style="display:inline">
                        @csrf
                        <button type="submit" class="link-button">gửi lại email xác thực</button>
                    </form>.
                </div>
            @endunless
        @endauth

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
