@extends('layouts.app')

@section('title', 'Thanh toán')

@section('content')
    <h1 class="page-title">Thông tin thanh toán</h1>

<div class="checkout-voucher-box">
    <div class="checkout-voucher-box__heading">
        <span>🎟️ Mã giảm giá</span>
        @if ($voucher)
            <span class="checkout-voucher-box__applied">Đã áp dụng {{ $voucher->code }}</span>
        @endif
    </div>
    @if ($availableVouchers->isNotEmpty())
        <details class="checkout-voucher-dropdown">
            <summary>Chọn mã giảm giá <span>⌄</span></summary>
            <div class="checkout-voucher-list">
            <span class="checkout-voucher-list__label">Mã phù hợp với giỏ hàng của bạn</span>
            @foreach ($availableVouchers as $availableVoucher)
                <div class="checkout-voucher-option {{ $voucher?->id === $availableVoucher->id ? 'is-applied' : '' }}">
                    <div>
                        <strong>{{ $availableVoucher->code }}</strong>
                        <span>
                            @if ($availableVoucher->type === 'percent')
                                Giảm {{ rtrim(rtrim(number_format($availableVoucher->value, 2), '0'), '.') }}%
                            @else
                                Giảm {{ number_format($availableVoucher->value, 0, ',', '.') }}₫
                            @endif
                            · Đơn từ {{ number_format($availableVoucher->min_order_amount, 0, ',', '.') }}₫
                        </span>
                    </div>
                    @if ($voucher?->id !== $availableVoucher->id)
                        <form method="POST" action="{{ route('checkout.voucher.apply') }}">
                            @csrf
                            <input type="hidden" name="voucher_code" value="{{ $availableVoucher->code }}">
                            <button type="submit" class="checkout-voucher-option__button">Dùng mã</button>
                        </form>
                    @else
                        <span class="checkout-voucher-option__used">Đang dùng</span>
                    @endif
                </div>
            @endforeach
            </div>
        </details>
    @endif

    @if ($voucher)
        <form method="POST" action="{{ route('checkout.voucher.remove') }}" class="checkout-voucher-box__remove-form">
            @csrf @method('DELETE')
            <button type="submit" class="link-button">Gỡ mã giảm giá</button>
        </form>
    @else
        <form method="POST" action="{{ route('checkout.voucher.apply') }}" class="checkout-voucher-form">
            @csrf
            <input type="text" name="voucher_code" class="form-input" placeholder="Nhập mã voucher" value="{{ old('voucher_code') }}" required>
            <button type="submit" class="btn-secondary">Áp dụng</button>
        </form>
    @endif
</div>

<form method="POST" action="{{ route('checkout.store') }}" class="checkout-page">
    @csrf
    <div class="checkout-main">
        <div class="checkout-section">
            <h2>Địa chỉ giao hàng</h2>
            @foreach ($addresses as $address)
                <label class="checkout-option">
                    <input type="radio" name="address_id" value="{{ $address->id }}" data-shipping-fee="{{ $shippingFeesByAddress[$address->id] }}" {{ $loop->first ? 'checked' : '' }}>
                    <span>
                        <strong>{{ $address->recipient_name }} — {{ $address->phone }}</strong><br>
                        {{ $address->address_detail }}, {{ $address->ward }}, {{ $address->district }}, {{ $address->province }}
                        @if ($address->is_default)<span class="checkout-option__badge">Mặc định</span>@endif
                        @php($addressDistance = \App\Services\ShippingFeeCalculator::distanceInKm($address->latitude, $address->longitude))
                        @if ($addressDistance !== null)
                            <small class="checkout-option__meta">Cách kho khoảng {{ number_format($addressDistance, 1, ',', '.') }} km</small>
                        @else
                            <small class="checkout-option__meta">Chưa ghim vị trí — phí tạm tính theo khu vực</small>
                        @endif
                    </span>
                    <span class="address-summary__arrow">›</span>
                </button>
            </div>

            {{-- Giá trị thật gửi kèm đơn hàng, luôn đồng bộ với lựa chọn ở trên --}}
            <input type="hidden" name="address_id" value="{{ $selectedAddressId }}">

            <div class="form-group">
                <label for="shipping_region">Khu vực giao hàng</label>
                <select name="shipping_region" id="shipping_region" form="region-form" onchange="this.form.submit()">
                    @foreach ($regionOptions as $value => $label)
                        <option value="{{ $value }}" {{ $selectedRegion == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="form-hint">Đơn từ 500.000₫ được miễn phí vận chuyển.</p>
                {{-- Giá trị thật gửi kèm đơn hàng, luôn đồng bộ với lựa chọn ở trên --}}
                <input type="hidden" name="shipping_region" value="{{ $selectedRegion }}">
            </div>

            <div class="form-group">
    <label>Phương thức thanh toán</label>
    <div class="payment-method-list">
        <label class="payment-method-option">
            <input type="radio" name="payment_method" value="cod" checked>
            <span class="payment-method-option__label">Thanh toán khi nhận hàng</span>
            <span class="payment-method-option__icon">💵</span>
        </label>
        <label class="payment-method-option">
            <input type="radio" name="payment_method" value="bank_transfer">
            <span class="payment-method-option__label">Chuyển khoản ngân hàng</span>
            <span class="payment-method-option__icon">🏦</span>
        </label>
        <label class="payment-method-option">
            <input type="radio" name="payment_method" value="qr">
            <span class="payment-method-option__label">Thanh toán bằng QR</span>
            <span class="payment-method-option__icon">📱</span>
        </label>
    </div>
    </div>

            <button type="submit" class="btn-primary">Đặt hàng</button>
        </form>

        {{-- Form ẩn riêng để đổi khu vực giao hàng, chỉ tính lại phí ship, chưa đặt hàng thật --}}
        <form id="region-form" action="{{ route('checkout.region') }}" method="POST" style="display:none;">
            @csrf
        </form>

        {{-- Form ẩn riêng để đổi địa chỉ đang chọn, lưu tạm vào session --}}
        <form id="address-form" action="{{ route('checkout.address') }}" method="POST" style="display:none;">
            @csrf
        </form>

        <div class="checkout-summary">
            <h2>Đơn hàng của bạn</h2>

            @foreach ($selectedItems as $item)
                <div class="checkout-summary__item">
                    <span>{{ $item->variant->product->name }} ({{ $item->variant->size }} - {{ $item->variant->color }}) x{{ $item->quantity }}</span>
                    <span>{{ number_format($item->variant->final_price * $item->quantity, 0, ',', '.') }}₫</span>
                </div>
            @endforeach
            <a href="{{ route('addresses.create') }}" class="auth-link">+ Thêm địa chỉ mới</a>
        </div>

        <div class="checkout-section">
            <h2>Phương thức thanh toán</h2>
            <label class="checkout-option"><input type="radio" name="payment_method" value="cod" checked><span><strong>Thanh toán khi nhận hàng (COD)</strong><br>Trả tiền mặt cho shipper khi nhận hàng.</span></label>
            <label class="checkout-option"><input type="radio" name="payment_method" value="vietqr"><span><strong>Chuyển khoản ngân hàng (VietQR)</strong><br>Quét mã QR và chuyển khoản từ ứng dụng ngân hàng.</span></label>
            <label class="checkout-option"><input type="radio" name="payment_method" value="vnpay"><span><strong>VNPay Sandbox</strong><br>Thanh toán qua VNPay trong môi trường thử nghiệm.</span></label>
        </div>

        <div class="checkout-section">
            <h2>Ghi chú (không bắt buộc)</h2>
            <textarea name="note" rows="3" class="form-input" placeholder="Ghi chú cho đơn hàng..."></textarea>
        </div>
    </div>

    <div class="checkout-summary">
        <h2>Đơn hàng của bạn</h2>
        @foreach ($cartItems as $item)
            <div class="checkout-summary__item">
                <span>{{ $item->variant->product->name }} ({{ $item->variant->size }}, {{ $item->variant->color }}) x{{ $item->quantity }}</span>
                <span>{{ number_format($item->subtotal, 0, ',', '.') }}₫</span>
            </div>
        @endforeach
        <div class="checkout-summary__row"><span>Tạm tính</span><span>{{ number_format($subtotal, 0, ',', '.') }}₫</span></div>
        <div class="checkout-summary__row"><span>Phí vận chuyển</span><span id="checkout-shipping-fee">{{ number_format($shippingFee, 0, ',', '.') }}₫</span></div>
        <small class="checkout-summary__hint">
            Phí tính từ {{ config('services.shipping.warehouse_name') }}:
            ≤5km 20.000₫ · ≤15km 30.000₫ · ≤30km 45.000₫ · ≤60km 65.000₫ · &gt;60km 90.000₫.
            Đơn từ {{ number_format(config('services.shipping.free_threshold'), 0, ',', '.') }}₫ được miễn phí ship.
        </small>
        @if ($discount > 0)
            <div class="checkout-summary__row checkout-summary__row--discount"><span>Giảm giá{{ $voucher ? ' ('.$voucher->code.')' : '' }}</span><span>-{{ number_format($discount, 0, ',', '.') }}₫</span></div>
        @endif
        <div class="checkout-summary__row checkout-summary__row--total"><span>Tổng cộng</span><span id="checkout-total">{{ number_format($total, 0, ',', '.') }}₫</span></div>
        <button type="submit" class="btn-primary">Đặt hàng</button>
    </div>
</form>

@push('scripts')
<script>
    document.querySelectorAll('input[name="address_id"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            const fee = Number(this.dataset.shippingFee || 0);
            const subtotal = {{ $subtotal }};
            const discount = {{ $discount }};
            const format = value => new Intl.NumberFormat('vi-VN').format(value) + '₫';
            document.getElementById('checkout-shipping-fee').textContent = format(fee);
            document.getElementById('checkout-total').textContent = format(subtotal + fee - discount);
        });
    });
</script>
@endpush
@endsection
