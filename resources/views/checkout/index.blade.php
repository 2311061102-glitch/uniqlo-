@extends('layouts.app')

@section('title', 'Thanh toán')

@section('content')
<div class="checkout-shell">
    <div class="checkout-pick-banner">
        <div class="checkout-pick-banner__mark">↗</div>
        <div>
            <strong>ORDER &amp; PICK</strong>
            <span>Đặt hàng online — nhận hàng nhanh chóng tại cửa hàng.</span>
        </div>
        <a href="{{ route('products.index') }}">Tiếp tục mua sắm →</a>
    </div>

    <div class="checkout-intro">
        <div>
            <p class="cart-eyebrow">MY UNIS</p>
            <h1>Thanh toán</h1>
            <p>Hoàn tất thông tin để nhận đơn hàng của bạn.</p>
        </div>
    </div>

<div class="checkout-voucher-box">
    <div class="checkout-voucher-box__heading">
        <span>🎟️ Mã giảm giá</span>
        @if ($voucher)
            <span class="checkout-voucher-box__applied">Đã áp dụng {{ $voucher->code }}</span>
        @endif
    </div>
    <details class="checkout-voucher-dropdown">
            <summary>Chọn mã giảm giá <span>⌄</span></summary>
            <div class="checkout-voucher-list">
            <span class="checkout-voucher-list__label">Mã phù hợp với giỏ hàng của bạn</span>
            @forelse ($availableVouchers as $availableVoucher)
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
            @empty
                <div class="checkout-voucher-empty">Hiện chưa có mã giảm giá phù hợp với giỏ hàng này. Bạn vẫn có thể nhập mã thủ công bên dưới.</div>
            @endforelse
            </div>
        </details>

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
                @php($addressBranch = $nearestBranchesByAddress[$address->id] ?? null)
                <label class="checkout-option">
                    <input type="radio" name="address_id" value="{{ $address->id }}" data-shipping-fee="{{ $shippingFeesByAddress[$address->id] }}" data-shipping-branch="{{ $addressBranch['name'] ?? 'Chi nhánh gần nhất' }}" data-shipping-branch-address="{{ $addressBranch['address'] ?? '' }}" {{ $loop->first ? 'checked' : '' }}>
                    <span>
                        <strong>{{ $address->recipient_name }} — {{ $address->phone }}</strong><br>
                        {{ $address->address_detail }}, {{ $address->ward }}, {{ $address->district }}, {{ $address->province }}
                        @if ($address->is_default)<span class="checkout-option__badge">Mặc định</span>@endif
                        @php($addressDistance = \App\Services\ShippingFeeCalculator::distanceInKm($address->latitude, $address->longitude))
                        @if ($addressDistance !== null)
                            <small class="checkout-option__meta">Cách {{ $addressBranch['name'] ?? 'shop' }} khoảng {{ number_format($addressDistance, 1, ',', '.') }} km</small>
                        @else
                            <small class="checkout-option__meta">Chưa ghim vị trí — phí tạm tính theo khu vực</small>
                        @endif
                    </span>
                </label>
            @endforeach
            <a href="{{ route('addresses.create') }}" class="auth-link">+ Thêm địa chỉ mới</a>
        </div>

        <div class="checkout-section">
            <h2>Phương thức thanh toán</h2>
            <label class="checkout-option"><input type="radio" name="payment_method" value="cod" checked><span><strong>Thanh toán khi nhận hàng (COD)</strong><br>Trả tiền mặt cho shipper khi nhận hàng. Đơn được xác nhận ngay, không cần duyệt thanh toán.</span></label>
            <label class="checkout-option"><input type="radio" name="payment_method" value="vietqr"><span><strong>Chuyển khoản ngân hàng (VietQR)</strong><br>Quét mã QR; hệ thống tự động duyệt ngay khi ngân hàng báo nhận đủ tiền.</span></label>
            <label class="checkout-option"><input type="radio" name="payment_method" value="vnpay"><span><strong>VNPay Sandbox</strong><br>Thanh toán liên kết; đơn tự động xác nhận sau khi VNPay gửi IPN thành công.</span></label>
        </div>

        <div class="shipping-fee-card">
            <div class="shipping-fee-card__heading">
                <div>
                    <h2>Phí giao hàng</h2>
                    <p id="checkout-nearest-branch">Đơn hàng sẽ được xử lý từ {{ $nearestBranch['name'] ?? 'chi nhánh gần nhất' }}{{ $nearestBranch ? ' ('.$nearestBranch['address'].')' : '' }}.</p>
                </div>
                <span class="shipping-fee-card__shop">Từ shop</span>
            </div>
            <div class="shipping-fee-table" role="table" aria-label="Bảng phí giao hàng">
                <div class="shipping-fee-table__row shipping-fee-table__row--head" role="row"><span>Khoảng cách</span><span>Phí giao hàng</span></div>
                @foreach (\App\Services\ShippingFeeCalculator::distanceFeeTiers() as $tier)
                    <div class="shipping-fee-table__row" role="row"><span>{{ $tier['label'] }}</span><strong>{{ $tier['fee'] === 0 ? 'Miễn phí' : number_format($tier['fee'], 0, ',', '.') . '₫' }}</strong></div>
                @endforeach
            </div>
            <p class="shipping-fee-card__free">Đơn hàng từ {{ number_format(config('services.shipping.free_threshold'), 0, ',', '.') }}₫ được miễn phí giao hàng.</p>
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
        <small class="checkout-summary__hint">Xử lý tại {{ $nearestBranch['name'] ?? 'chi nhánh gần nhất' }} · Tính theo khoảng cách từ chi nhánh.</small>
        @if ($discount > 0)
            <div class="checkout-summary__row checkout-summary__row--discount"><span>Giảm giá{{ $voucher ? ' ('.$voucher->code.')' : '' }}</span><span>-{{ number_format($discount, 0, ',', '.') }}₫</span></div>
        @endif
        <div class="checkout-summary__row checkout-summary__row--total"><span>Tổng cộng</span><span id="checkout-total">{{ number_format($total, 0, ',', '.') }}₫</span></div>
        <button type="submit" class="btn-primary">Đặt hàng</button>
    </div>
</form>
</div>

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
            const branchName = this.dataset.shippingBranch || 'chi nhánh gần nhất';
            const branchAddress = this.dataset.shippingBranchAddress ? ` (${this.dataset.shippingBranchAddress})` : '';
            document.getElementById('checkout-nearest-branch').textContent = `Đơn hàng sẽ được xử lý từ ${branchName}${branchAddress}.`;
        });
    });
</script>
@endpush
@endsection
