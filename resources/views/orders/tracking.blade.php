@extends('layouts.app')

@section('title', 'Theo dõi đơn hàng '.$order->order_code)

@section('content')
<div class="tracking-page">
    <div class="tracking-header">
        <div><p class="account-panel__eyebrow">Đơn hàng {{ $order->order_code }}</p><h1>Theo dõi đơn hàng</h1></div>
        <a href="{{ route('orders.show', $order) }}">Xem chi tiết</a>
    </div>

    @if ($order->order_status === 'cancelled')
        <div class="alert alert--error">Đơn hàng đã được hủy.</div>
    @else
        <p class="tracking-current">Trạng thái hiện tại: <strong>{{ match ($order->order_status) { 'pending' => 'Chờ xác nhận', 'confirmed' => 'Đã xác nhận', 'shipping' => 'Đang giao hàng', 'completed' => 'Đã giao hàng' } }}</strong></p>
        <div class="tracking-timeline">
            @foreach ($steps as $index => $step)
                <div class="tracking-step {{ $index < $currentIndex ? 'tracking-step--done' : '' }} {{ $index === $currentIndex ? 'tracking-step--current' : '' }}">
                    <div class="tracking-step__marker">{{ $index < $currentIndex ? '✓' : $index + 1 }}</div>
                    <div><h2>{{ $step['title'] }}</h2><p>{{ $step['description'] }}</p>@if ($index === 0)<small>{{ $order->created_at->format('d/m/Y H:i') }}</small>@endif</div>
                </div>
            @endforeach
        </div>
        <p class="tracking-note">Tiến độ trên là trạng thái mô phỏng dùng cho bài tập lớn.</p>
    @endif
</div>
@endsection