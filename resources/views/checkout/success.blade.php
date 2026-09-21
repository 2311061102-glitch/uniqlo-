@extends('layouts.app')

@section('title', 'Đặt hàng thành công')

@section('content')
    <div class="success-page">
        <h1 class="page-title">✅ Đặt hàng thành công!</h1>

        <div class="order-summary">
            <p><strong>Mã đơn hàng:</strong> {{ $order->order_code }}</p>
            <p><strong>Người nhận:</strong> {{ $order->recipient_name }} - {{ $order->recipient_phone }}</p>
            <p><strong>Địa chỉ:</strong> {{ $order->fullAddress() }}</p>
            <p><strong>Phương thức thanh toán:</strong>
                {{ $order->payment_method === 'cod' ? 'Thanh toán khi nhận hàng (COD)' : ($order->payment_method === 'qr' ? 'Thanh toán QR' : 'Chuyển khoản ngân hàng') }}
            </p>
            <p><strong>Trạng thái đơn hàng:</strong> <span class="status-badge status-badge--{{ $order->order_status }}">{{ $order->order_status }}</span></p>

            <h2>Chi tiết đơn hàng</h2>
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
        </div>

        <div class="success-page__actions">
            <a href="{{ route('orders.show', $order) }}" class="btn-primary btn-primary--inline">Xem đơn hàng</a>
            <a href="{{ route('products.index') }}" class="btn-secondary">Tiếp tục mua hàng</a>
        </div>
    </div>
@endsection