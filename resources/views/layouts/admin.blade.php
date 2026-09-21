<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị') - UNIQLO Admin</title>
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cart-checkout-orders.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="admin-body">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="admin-sidebar__logo">UNIQLO <span>Admin</span></div>
            <nav class="admin-sidebar__nav">
                <a href="{{ route('admin.vouchers.index') }}"
                   class="{{ request()->routeIs('admin.vouchers.*') ? 'is-active' : '' }}">
                    🎟 Voucher / Mã giảm giá
                </a>
            </nav>
            <a href="{{ route('home') }}" class="admin-sidebar__back">← Về trang khách hàng</a>
        </aside>

        <main class="admin-content">
            @if (session('success'))
                <div class="alert alert--success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert--error">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>