@extends('layouts.app')

@section('title', 'Đơn hàng '.$order->order_code)

@section('content')
<h1 class="page-title">Đơn hàng {{ $order->order_code }}</h1>

@auth @if(auth()->user()->isAdmin())<p><a href="{{ route('admin.orders.show', $order) }}" class="admin-inline-edit">⚙ Quản trị đơn hàng</a></p>@endif @endauth
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
            @if($order->fulfillment_branch_name)
                <p class="form-hint"><strong>Chi nhánh xử lý:</strong> {{ $order->fulfillment_branch_name }} · {{ $order->fulfillment_branch_address }}</p>
            @endif
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

        @if (in_array($order->order_status, ['pending', 'confirmed'], true))
            <a href="{{ route('orders.cancel.confirm', $order) }}" class="btn-danger" style="display:inline-block; margin-top:16px; text-decoration:none;">Hủy đơn hàng</a>
        @endif

    </div>
</div>

<p class="auth-card__footer">
    <a href="{{ route('orders.index') }}">← Quay lại danh sách đơn hàng</a>
</p>
@endsection
