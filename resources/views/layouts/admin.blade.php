<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị') - UNIS Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/site.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cart-checkout-orders.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="admin-body">
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <a class="admin-sidebar__logo" href="{{ route('admin.dashboard') }}">UNIS <span>Admin</span></a>
            <div class="admin-sidebar__workspace">BẢNG ĐIỀU KHIỂN</div>
            <nav class="admin-sidebar__nav">
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}"><span>▦</span> Tổng quan</a>
                <a href="{{ route('admin.products.index') }}"><span>◇</span> Sản phẩm</a>
                <a href="{{ route('admin.orders.index') }}"><span>□</span> Đơn hàng</a>
                <a href="{{ route('admin.categories.index') }}"><span>⊞</span> Danh mục</a>
                <a href="{{ route('admin.customers.index') }}"><span>♙</span> Khách hàng</a>
                <a href="{{ route('admin.permissions') }}" class="{{ request()->routeIs('admin.permissions') ? 'is-active' : '' }}"><span>⚿</span> Phân quyền</a>
                <a href="{{ route('admin.vouchers.index') }}" class="{{ request()->routeIs('admin.vouchers.*') ? 'is-active' : '' }}"><span>◇</span> Khuyến mãi</a>
            </nav>
            <div class="admin-sidebar__user"><span>{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><div><strong>{{ auth()->user()->name }}</strong><small>Quản trị viên</small></div></div>
            <a href="{{ route('home') }}" class="admin-sidebar__back">← Về trang khách hàng</a>
        </aside>
        <main class="admin-content">
            @if (session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
            @if (session('error'))<div class="alert alert--error">{{ session('error') }}</div>@endif
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
