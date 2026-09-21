@extends('layouts.app')

@section('title', 'Đơn hàng ' . $order->order_code)

@section('content')
    <h1 class="page-title">Đơn hàng {{ $order->order_code }}</h1>

    @if (session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert--error">{{ session('error') }}</div>
    @endif

    <div class="order-summary">
        <p><strong>Ngày đặt:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</p>
        <p><strong>Người nhận:</strong> {{ $order->recipient_name }} - {{ $order->recipient_phone }}</p>
        <p><strong>Địa chỉ:</strong> {{ $order->fullAddress() }}</p>
        <p><strong>Phương thức thanh toán:</strong> {{ $order->payment_method === 'cod' ? 'COD' : ($order->payment_method === 'qr' ? 'QR' : 'Chuyển khoản') }}</p>
        <p><strong>Trạng thái thanh toán:</strong> {{ $order->payment_status }}</p>
        <p><strong>Trạng thái đơn hàng:</strong> <span class="status-badge status-badge--{{ $order->order_status }}">{{ $order->order_status }}</span></p>

        <h2>Chi tiết sản phẩm</h2>
        <table class="order-detail-table">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Đơn giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td>{{ $item->product_name }} ({{ $item->variant_label }})</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->price, 0, ',', '.') }}₫</td>
                        <td>{{ number_format($item->subtotal, 0, ',', '.') }}₫</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="checkout-summary__line">
            <span>Tạm tính</span>
            <span>{{ number_format($order->subtotal_amount, 0, ',', '.') }}₫</span>
        </div>
        <div class="checkout-summary__line">
            <span>Phí vận chuyển</span>
            <span>{{ $order->shipping_fee > 0 ? number_format($order->shipping_fee, 0, ',', '.') . '₫' : 'Miễn phí' }}</span>
        </div>
        @if ($order->discount_amount > 0)
            <div class="checkout-summary__line checkout-summary__line--discount">
                <span>Giảm giá{{ $order->voucher ? ' (' . $order->voucher->code . ')' : '' }}</span>
                <span>-{{ number_format($order->discount_amount, 0, ',', '.') }}₫</span>
            </div>
        @endif

        <p class="checkout-summary__total">Tổng tiền: <span>{{ number_format($order->total_amount, 0, ',', '.') }}₫</span></p>

        @if ($order->canBeCancelled())
            <form action="{{ route('orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Bạn chắc chắn muốn hủy đơn hàng này?');" class="cancel-order-form">
                @csrf
                <button type="submit" class="btn-remove">Hủy đơn hàng</button>
            </form>
        @endif
    </div>

    <a href="{{ route('orders.index') }}" class="link-button">&larr; Quay lại danh sách đơn hàng</a>
@endsection