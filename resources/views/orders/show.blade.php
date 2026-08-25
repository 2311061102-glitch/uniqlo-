@extends('layouts.app')

@section('title', 'Đơn hàng '.$order->order_code)

@section('content')
<h1 class="page-title">Đơn hàng {{ $order->order_code }}</h1>

<div class="order-detail">
    <div class="order-detail__main">
        <div class="checkout-section">
            <h2>Sản phẩm</h2>
            @foreach ($order->items as $item)
                <div class="checkout-summary__item">
                    <span>{{ $item->product_name }} ({{ $item->variant_size }}, {{ $item->variant_color }}) x{{ $item->quantity }}</span>
                    <span>{{ number_format($item->subtotal, 0, ',', '.') }}₫</span>
                </div>
            @endforeach
        </div>

        <div class="checkout-section">
            <h2>Địa chỉ giao hàng</h2>
            <p>{{ $order->recipient_name }} — {{ $order->phone }}</p>
            <p class="form-hint">{{ $order->address_detail }}, {{ $order->ward }}, {{ $order->district }}, {{ $order->province }}</p>
        </div>

        @if ($order->note)
            <div class="checkout-section">
                <h2>Ghi chú</h2>
                <p>{{ $order->note }}</p>
            </div>
        @endif
    </div>

    <div class="checkout-summary">
        <h2>Tóm tắt</h2>
        <div class="checkout-summary__row">
            <span>Tạm tính</span>
            <span>{{ number_format($order->subtotal, 0, ',', '.') }}₫</span>
        </div>
        <div class="checkout-summary__row">
            <span>Phí vận chuyển</span>
            <span>{{ number_format($order->shipping_fee, 0, ',', '.') }}₫</span>
        </div>
        <div class="checkout-summary__row checkout-summary__row--total">
            <span>Tổng cộng</span>
            <span>{{ number_format($order->total_amount, 0, ',', '.') }}₫</span>
        </div>

        <p class="form-hint" style="margin-top: 16px; line-height: 1.8;">
            Phương thức: <strong>{{ strtoupper($order->payment_method) }}</strong><br>
            Trạng thái thanh toán: <strong>{{ $order->payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}</strong><br>
            Trạng thái đơn:
            <span class="order-status-badge order-status-badge--{{ $order->order_status }}">
                {{ match($order->order_status) {
                    'pending' => 'Chờ xác nhận',
                    'confirmed' => 'Đã xác nhận',
                    'shipping' => 'Đang giao',
                    'completed' => 'Hoàn thành',
                    'cancelled' => 'Đã hủy',
                } }}
            </span>
        </p>

        @if ($order->payment_method === 'vietqr' && $order->payment_status !== 'paid')
            <a href="{{ route('payments.vietqr', $order) }}" class="btn-primary btn-primary--inline" style="margin-top: 16px; display: block; text-align: center;">
                Xem lại mã QR chuyển khoản
            </a>
        @endif

        @if ($order->order_status === 'pending')
            <form method="POST" action="{{ route('orders.cancel', $order) }}" style="margin-top: 16px;"
                  onsubmit="return confirm('Hủy đơn hàng này?');">
                @csrf
                <button type="submit" class="btn-danger">Hủy đơn hàng</button>
            </form>
        @endif

        @if ($order->order_status !== 'cancelled')
            <a href="{{ route('orders.tracking', $order) }}" class="btn-secondary order-track-link">Theo dõi tiến độ giao hàng</a>
        @endif

        @auth
            @if (auth()->user()->isAdmin() && $order->payment_status !== 'paid' && $order->payment_method !== 'cod')
                <form method="POST" action="{{ route('orders.confirmPayment', $order) }}" style="margin-top: 16px;">
                    @csrf
                    <button type="submit" class="btn-primary">[Demo Admin] Xác nhận đã thanh toán</button>
                </form>
            @endif
        @endauth
    </div>
</div>

<p class="auth-card__footer">
    <a href="{{ route('orders.index') }}">← Quay lại danh sách đơn hàng</a>
</p>
@endsection
