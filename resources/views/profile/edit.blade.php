@extends('layouts.app')

@section('title', 'Tài khoản')

@section('content')
<div class="account-page">
    <aside class="account-sidebar">
        <div class="account-sidebar__identity">
            <div class="account-sidebar__avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div><strong>{{ $user->name }}</strong><span>{{ $user->email }}</span></div>
        </div>
        <nav class="account-menu" aria-label="Mục tài khoản">
            <button type="button" class="account-menu__item account-menu__item--active" data-account-tab="profile">Thông tin cá nhân</button>
            <button type="button" class="account-menu__item" data-account-tab="addresses">Sổ địa chỉ <span>{{ $addresses->count() }}</span></button>
            <button type="button" class="account-menu__item" data-account-tab="orders">Đơn hàng của tôi <span>{{ $orders->count() }}</span></button>
            <button type="button" class="account-menu__item" data-account-tab="security">Bảo mật tài khoản</button>
        </nav>
    </aside>

    <div class="account-content">
        <section class="account-panel account-panel--active" data-account-panel="profile">
            <div class="account-panel__heading"><div><p class="account-panel__eyebrow">Tài khoản</p><h1>Thông tin cá nhân</h1></div></div>
            <div class="profile-avatar">
                @if ($user->avatar)<img src="{{ asset('storage/'.$user->avatar) }}" alt="Avatar" class="profile-avatar__img">@else<div class="profile-avatar__placeholder">{{ strtoupper(substr($user->name, 0, 1)) }}</div>@endif
            </div>
            <form method="POST" action="{{ route('profile.update') }}" class="auth-form" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="account-form-grid">
                    <div class="form-group"><label for="name">Họ và tên</label><input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-input @error('name') form-input--error @enderror" required>@error('name')<p class="form-error">{{ $message }}</p>@enderror</div>
                    <div class="form-group"><label for="phone">Số điện thoại</label><input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input @error('phone') form-input--error @enderror" required>@error('phone')<p class="form-error">{{ $message }}</p>@enderror</div>
                    <div class="form-group"><label>Email</label><input type="email" value="{{ $user->email }}" class="form-input" disabled><p class="form-hint">Email không thể thay đổi.</p></div>
                    <div class="form-group"><label for="avatar">Ảnh đại diện</label><input type="file" id="avatar" name="avatar" class="form-input" accept="image/*">@error('avatar')<p class="form-error">{{ $message }}</p>@enderror</div>
                </div>
                <button type="submit" class="btn-primary">Lưu thay đổi</button>
            </form>
        </section>

        <section class="account-panel" data-account-panel="addresses">
            <div class="account-panel__heading"><div><p class="account-panel__eyebrow">Giao hàng</p><h1>Sổ địa chỉ</h1></div><a href="{{ route('addresses.create') }}" class="btn-primary btn-primary--inline">+ Thêm địa chỉ</a></div>
            @if ($addresses->isEmpty())<p class="form-hint">Bạn chưa có địa chỉ giao hàng nào.</p>@else
                <div class="address-list">
                    @foreach ($addresses as $address)
                        <div class="address-card">
                            @if ($address->is_default)<span class="address-card__badge">Mặc định</span>@endif
                            <p class="address-card__name">{{ $address->recipient_name }} — {{ $address->phone }}</p>
                            <p class="address-card__detail">{{ $address->address_detail }}, {{ $address->ward }}, {{ $address->district }}, {{ $address->province }}</p>
                            <div class="address-card__actions"><a href="{{ route('addresses.edit', $address) }}">Sửa</a>@if (! $address->is_default)<form method="POST" action="{{ route('addresses.setDefault', $address) }}">@csrf @method('PATCH')<button type="submit" class="link-button">Đặt làm mặc định</button></form>@endif<form method="POST" action="{{ route('addresses.destroy', $address) }}" onsubmit="return confirm('Xóa địa chỉ này?');">@csrf @method('DELETE')<button type="submit" class="link-button link-button--danger">Xóa</button></form></div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="account-panel" data-account-panel="orders">
            <div class="account-panel__heading"><div><p class="account-panel__eyebrow">Lịch sử mua sắm</p><h1>Đơn hàng của tôi</h1></div><a href="{{ route('orders.index') }}">Xem tất cả</a></div>
            @if ($orders->isEmpty())<p class="form-hint">Bạn chưa có đơn hàng nào. <a href="{{ route('products.index') }}">Mua sắm ngay</a>.</p>@else
                <div class="order-list">
                    @foreach ($orders as $order)
                        <a href="{{ route('orders.show', $order) }}" class="order-list__item"><div><p class="order-list__code">{{ $order->order_code }}</p><p class="form-hint">{{ $order->created_at->format('d/m/Y H:i') }}</p></div><span class="order-status-badge order-status-badge--{{ $order->order_status }}">{{ match($order->order_status) { 'pending' => 'Chờ xác nhận', 'confirmed' => 'Đã xác nhận', 'shipping' => 'Đang giao', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy' } }}</span><strong>{{ number_format($order->total_amount, 0, ',', '.') }}₫</strong></a>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="account-panel" data-account-panel="security">
            <div class="account-panel__heading"><div><p class="account-panel__eyebrow">Bảo mật</p><h1>Bảo mật tài khoản</h1></div></div>
            <a href="{{ route('password.edit') }}" class="btn-secondary account-security-link">Đổi mật khẩu</a>
            <div class="danger-zone"><h2 class="danger-zone__title">Xóa tài khoản</h2><p class="form-hint">Hành động này không thể hoàn tác.</p><form method="POST" action="{{ route('profile.destroy') }}" class="auth-form" onsubmit="return confirm('Bạn chắc chắn muốn xóa tài khoản?');">@csrf @method('DELETE')<div class="form-group"><label for="current_password_delete">Nhập mật khẩu để xác nhận</label><div class="password-field"><input type="password" id="current_password_delete" name="current_password" class="form-input @error('current_password') form-input--error @enderror" required><button type="button" class="password-toggle" aria-label="Hiện mật khẩu" title="Hiện mật khẩu">&#128065;</button></div>@error('current_password')<p class="form-error">{{ $message }}</p>@enderror</div><button type="submit" class="btn-danger">Xóa tài khoản vĩnh viễn</button></form></div>
        </section>
    </div>
</div>

@push('scripts')
<script>
    document.querySelectorAll('[data-account-tab]').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('[data-account-tab]').forEach(item => item.classList.remove('account-menu__item--active'));
            document.querySelectorAll('[data-account-panel]').forEach(panel => panel.classList.remove('account-panel--active'));
            tab.classList.add('account-menu__item--active');
            document.querySelector('[data-account-panel="' + tab.dataset.accountTab + '"]').classList.add('account-panel--active');
        });
    });
</script>
@endpush
@endsection
