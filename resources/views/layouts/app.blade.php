<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'UNIQLO Men - Đồ án')</title>

    <link rel="stylesheet" href="{{ asset('css/uniqlo-full-combined.css') }}">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
</head>
<body>

    <header class="site-header">
        <a href="{{ route('home') }}" class="site-header__logo">UNIQLO</a>
        <nav class="site-header__nav">
            <form class="search-form" action="{{ route('products.index') }}" method="GET" autocomplete="off">
                <input id="site-search" name="q" value="{{ request('q') }}" placeholder="Tìm sản phẩm..." aria-label="Tìm sản phẩm">
                <div id="search-suggestions" class="search-suggestions"></div>
            </form>
            <a href="{{ route('products.index') }}">Sản phẩm</a>
            <a href="{{ route('categories.index') }}">Danh mục</a>

            @auth
                <a href="{{ route('wishlist.index') }}">Yêu thích</a>
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

    @stack('scripts')
    <script>
        const searchInput = document.getElementById('site-search');
        const suggestionBox = document.getElementById('search-suggestions');
        let suggestionTimer;
        searchInput?.addEventListener('input', function () {
            clearTimeout(suggestionTimer);
            if (this.value.trim().length < 2) { suggestionBox.innerHTML = ''; return; }
            suggestionTimer = setTimeout(() => fetch(`{{ route('products.suggestions') }}?q=${encodeURIComponent(this.value)}`)
                .then(response => response.json())
                .then(items => suggestionBox.innerHTML = items.map(item => `<a href="/san-pham/${item.slug}">${item.name}</a>`).join('')),
            180);
        });
    </script>
</body>
</html>
