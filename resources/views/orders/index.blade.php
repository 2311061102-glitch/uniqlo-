@extends('layouts.app')

@section('title', 'Đơn hàng của tôi')

@section('content')
<h1 class="page-title">Đơn hàng của tôi</h1>

@if ($orders->isEmpty())
    <div class="empty-state">
        Bạn chưa có đơn hàng nào. <a href="{{ route('products.index') }}">Mua sắm ngay</a>.
    </div>
@else
    <div class="order-list">
        @foreach ($orders as $order)
            <a href="{{ route('orders.show', $order) }}" class="order-list__item">
                <div>
                    <p class="order-list__code">{{ $order->order_code }}</p>
                    <p class="form-hint">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <span class="order-status-badge order-status-badge--{{ $order->order_status }}">
                    {{ match($order->order_status) {
                        'pending' => 'Chờ xác nhận',
                        'confirmed' => 'Đã xác nhận',
                        'shipping' => 'Đang giao',
                        'completed' => 'Hoàn thành',
                        'cancelled' => 'Đã hủy',
                    } }}
                </span>
                <strong>{{ number_format($order->total_amount, 0, ',', '.') }}₫</strong>
            </a>
        @endforeach
    </div>

    <div class="pagination-wrapper">{{ $orders->links() }}</div>
@endif
@endsection
