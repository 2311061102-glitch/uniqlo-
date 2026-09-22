@extends('layouts.app')

@section('title', 'Thanh toán chuyển khoản')

@section('content')
<div class="auth-page">
    <div class="auth-card" style="max-width: 480px;">
        <h1 class="auth-card__title">Quét mã để chuyển khoản</h1>

        <p class="form-hint" style="margin-bottom: 16px;">
            Đơn hàng <strong>{{ $order->order_code }}</strong> — số tiền cần chuyển:
            <strong>{{ number_format($order->total_amount, 0, ',', '.') }}₫</strong>
        </p>

        <div style="text-align: center; margin-bottom: 16px;">
            <img src="{{ $qrUrl }}" alt="Mã QR chuyển khoản"
                 style="max-width: 100%; border: 1px solid var(--border-lines-color, #dadada); border-radius: 8px;">
        </div>

        <p class="form-hint">
            Mở app ngân hàng bất kỳ (hỗ trợ VietQR — hầu hết ngân hàng VN đều có), chọn
            <strong>Quét mã QR</strong>, quét mã ở trên rồi xác nhận chuyển khoản. Nội dung chuyển khoản
            đã tự điền sẵn mã đơn hàng <strong>{{ $order->order_code }}</strong> để đối soát, không cần tự gõ.
        </p>

        <p class="form-hint" style="margin-top: 16px;">
            Trạng thái thanh toán hiện tại:
            <strong>{{ $order->payment_status === 'paid' ? 'Đã thanh toán' : 'Đang chờ chuyển khoản' }}</strong>
        </p>

        <div id="qr-auto-status" class="payment-status-box" style="margin-top:16px;">
            <span>Hệ thống đang tự động kiểm tra giao dịch...</span>
        </div>

        <p class="auth-card__footer">
            <a href="{{ route('orders.show', $order) }}">Xem chi tiết đơn hàng</a>
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const box = document.getElementById('qr-auto-status');
    if (!box) return;
    const url = @json(route('checkout.status', $order));
    const orderUrl = @json(route('orders.show', $order));
    const timer = setInterval(() => fetch(url, {headers: {'Accept': 'application/json'}}).then(r => r.json()).then(data => {
        if (data.payment_status === 'paid') {
            clearInterval(timer);
            box.innerHTML = '<strong style="color:#16824f">Đã nhận tiền tự động. Đơn hàng đã được xác nhận.</strong>';
            setTimeout(() => window.location.href = orderUrl, 1200);
        } else if (data.expired) {
            clearInterval(timer);
            box.innerHTML = '<strong style="color:#b3261e">Mã QR đã hết hạn, đơn hàng đã được hủy.</strong>';
        }
    }).catch(() => {}), 3000);
})();
</script>
@endpush
