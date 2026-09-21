@extends('layouts.app')

@section('title', 'Đơn hàng của tôi')

@section('content')
    <h1 class="page-title">Đơn hàng của tôi</h1>

    @if ($orders->isEmpty())
        <div class="cart-empty">
            <p>Bạn chưa có đơn hàng nào.</p>
            <a href="{{ route('products.index') }}" class="btn-primary btn-primary--inline">Mua sắm ngay</a>
        </div>
    @else
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Ngày đặt</th>
                    <th>Tổng tiền</th>
                    <th>Thanh toán</th>
                    <th>Trạng thái</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td>{{ $order->order_code }}</td>
                        <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ number_format($order->total_amount, 0, ',', '.') }}₫</td>
                        <td>{{ $order->payment_method === 'cod' ? 'COD' : ($order->payment_method === 'qr' ? 'QR' : 'Chuyển khoản') }}</td>
                        <td><span class="status-badge status-badge--{{ $order->order_status }}">{{ $order->order_status }}</span></td>
                        <td><a href="{{ route('orders.show', $order) }}" class="link-button">Xem chi tiết</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="pagination-wrapper">
            {{ $orders->links() }}
        </div>
    @endif
@endsection