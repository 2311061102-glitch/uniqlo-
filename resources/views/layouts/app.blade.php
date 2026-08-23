<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'UNIQLO Men - Đồ án')</title>

    <link rel="stylesheet" href="{{ asset('css/uniqlo-full-combined.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>

    <header class="site-header">
        <a href="{{ route('home') }}" class="site-header__logo">UNIQLO</a>
        <nav class="site-header__nav">
            @auth
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

        @yield('content')
    </main>

</body>
</html>
