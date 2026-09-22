@extends('layouts.app')
@section('title', 'Hủy đơn hàng')
@section('content')
<div class="cancel-page"><div class="cancel-card"><div class="cancel-icon">!</div><h1>Hủy đơn hàng?</h1><p>Bạn đang yêu cầu hủy đơn <strong>#{{ $order->order_code }}</strong>.</p><p class="form-hint">Đơn chưa bắt đầu xử lý nên hàng sẽ được hoàn lại kho. Hành động này không thể hoàn tác.</p><div class="cancel-actions"><a href="{{ route('orders.show', $order) }}" class="btn-secondary">Quay lại</a><form method="POST" action="{{ route('orders.cancel', $order) }}">@csrf<button class="btn-danger">Xác nhận hủy đơn</button></form></div></div></div>
@endsection
