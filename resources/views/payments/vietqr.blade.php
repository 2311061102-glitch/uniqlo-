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

        {{--
            Nút này CHỈ hiện với tài khoản admin — dùng để demo/test luồng xác nhận
            thanh toán trong lúc chưa có trang Admin thật (Thành viên 4 sẽ làm sau).
            Khách hàng bình thường KHÔNG thấy và KHÔNG tự xác nhận được cho chính mình.
        --}}
        @auth
            @if (auth()->user()->isAdmin() && $order->payment_status !== 'paid')
                <form method="POST" action="{{ route('orders.confirmPayment', $order) }}" style="margin-top: 16px;">
                    @csrf
                    <button type="submit" class="btn-primary">
                        [Demo Admin] Xác nhận đã nhận được chuyển khoản
                    </button>
                </form>
            @endif
        @endauth

        <p class="auth-card__footer">
            <a href="{{ route('orders.show', $order) }}">Xem chi tiết đơn hàng</a>
        </p>
    </div>
</div>
@endsection
