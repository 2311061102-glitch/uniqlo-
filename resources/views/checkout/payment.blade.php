@extends('layouts.app')

@section('title', 'Xác nhận thanh toán')

@section('content')
    <div class="payment-page">
        <h1 class="page-title">
            {{ $order->payment_method === 'qr' ? 'Thanh toán QR (VietQR)' : 'Chuyển khoản ngân hàng' }}
        </h1>

        <p>Mã đơn hàng: <strong>{{ $order->order_code }}</strong></p>
        <p>Số tiền cần thanh toán: <strong>{{ number_format($order->total_amount, 0, ',', '.') }}₫</strong></p>

        <div class="qr-countdown" id="qr-countdown">
            Còn hiệu lực: <strong id="qr-countdown-time">--:--</strong>
        </div>

        @if ($order->payment_method === 'qr')
            {{-- ===== Phương thức QR: hiện ảnh mã quét ===== --}}
            <div class="qr-box">
                <img src="{{ $qrImageUrl }}" alt="Mã QR thanh toán VietQR cho đơn {{ $order->order_code }}">
            </div>

            <p>Quét mã bằng ứng dụng ngân hàng bất kỳ (hỗ trợ VietQR) để thanh toán.</p>
        @else
            {{-- ===== Phương thức Chuyển khoản: hiện thông tin để khách tự gõ tay ===== --}}
            <div class="bank-info-card">
                <div class="bank-info-row">
                    <span class="bank-info-row__label">Ngân hàng</span>
                    <span class="bank-info-row__value">{{ config('services.vietqr.bank_name') }}</span>
                </div>

                <div class="bank-info-row">
                    <span class="bank-info-row__label">Số tài khoản</span>
                    <span class="bank-info-row__value">
                        <span id="bank-account-no">{{ config('services.vietqr.account_no') }}</span>
                        <button type="button" class="copy-btn" data-copy-target="bank-account-no">Sao chép</button>
                    </span>
                </div>

                <div class="bank-info-row">
                    <span class="bank-info-row__label">Chủ tài khoản</span>
                    <span class="bank-info-row__value">{{ config('services.vietqr.account_name') }}</span>
                </div>

                <div class="bank-info-row">
                    <span class="bank-info-row__label">Số tiền</span>
                    <span class="bank-info-row__value">
                        <span id="bank-amount">{{ (int) $order->total_amount }}</span>
                        <button type="button" class="copy-btn" data-copy-target="bank-amount">Sao chép</button>
                    </span>
                </div>

                <div class="bank-info-row">
                    <span class="bank-info-row__label">Nội dung chuyển khoản</span>
                    <span class="bank-info-row__value">
                        <span id="bank-content">{{ $order->order_code }}</span>
                        <button type="button" class="copy-btn" data-copy-target="bank-content">Sao chép</button>
                    </span>
                </div>
            </div>

            <p>Mở app ngân hàng, chuyển khoản thủ công theo đúng thông tin trên.</p>
        @endif

        <p class="form-hint">
            Nội dung chuyển khoản phải chứa đúng mã đơn hàng — vui lòng
            <strong>giữ nguyên nội dung</strong> để hệ thống xác nhận đúng đơn.
            Giao dịch chỉ có hiệu lực trong <strong>10 phút</strong>, quá thời gian đơn hàng sẽ tự động bị hủy.
        </p>

        <div id="payment-status-box" class="payment-status-box">
            <div class="payment-status-box__spinner"></div>
            <span>Đang chờ xác nhận thanh toán tự động...</span>
        </div>

        @if (app()->environment('local'))
            <form action="{{ route('sepay.simulate', $order) }}" method="POST" class="dev-simulate-form">
                @csrf
                <button type="submit" class="btn-secondary">
                    🧪 [DEV] Giả lập đã chuyển khoản thành công
                </button>
            </form>
        @endif
    </div>

    @push('scripts')
    <script>
    (function () {
        const statusUrl = '{{ route("checkout.status", $order) }}';
        const successUrl = '{{ route("checkout.success", $order) }}';
        const cartUrl = '{{ route("cart.index") }}';
        const expiresAt = new Date('{{ $order->qr_expires_at?->toIso8601String() }}').getTime();

        const countdownEl = document.getElementById('qr-countdown-time');
        const countdownBox = document.getElementById('qr-countdown');

        function updateCountdown() {
            const remainingMs = expiresAt - Date.now();

            if (remainingMs <= 0) {
                countdownEl.textContent = '00:00';
                countdownBox.classList.add('qr-countdown--expired');
                return;
            }

            const totalSeconds = Math.floor(remainingMs / 1000);
            const minutes = String(Math.floor(totalSeconds / 60)).padStart(2, '0');
            const seconds = String(totalSeconds % 60).padStart(2, '0');
            countdownEl.textContent = `${minutes}:${seconds}`;

            if (totalSeconds <= 60) {
                countdownBox.classList.add('qr-countdown--warning');
            }
        }

        updateCountdown();
        const countdownInterval = setInterval(updateCountdown, 1000);

        const statusInterval = setInterval(() => {
            fetch(statusUrl)
                .then(res => res.json())
                .then(data => {
                    if (data.payment_status === 'paid') {
                        clearInterval(statusInterval);
                        clearInterval(countdownInterval);
                        window.location.href = successUrl;
                        return;
                    }

                    if (data.expired) {
                        clearInterval(statusInterval);
                        clearInterval(countdownInterval);
                        alert('Đã hết hạn (quá 10 phút chưa thanh toán). Đơn hàng đã bị hủy, vui lòng đặt lại.');
                        window.location.href = cartUrl;
                    }
                })
                .catch(() => {});
        }, 3000);

        // Nút "Sao chép" cho các trường thông tin chuyển khoản thủ công
        document.querySelectorAll('.copy-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.dataset.copyTarget;
                const text = document.getElementById(targetId).textContent.trim();
                navigator.clipboard.writeText(text).then(() => {
                    const original = btn.textContent;
                    btn.textContent = 'Đã sao chép!';
                    setTimeout(() => { btn.textContent = original; }, 1500);
                });
            });
        });
    })();
    </script>
    @endpush
@endsection