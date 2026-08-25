@extends('layouts.app')

@section('title', 'Thanh toán')

@section('content')
<h1 class="page-title">Thanh toán</h1>

<form method="POST" action="{{ route('checkout.store') }}" class="checkout-page">
    @csrf

    <div class="checkout-main">
        <div class="checkout-section">
            <h2>Địa chỉ giao hàng</h2>

            @foreach ($addresses as $address)
                <label class="checkout-option">
                    <input type="radio" name="address_id" value="{{ $address->id }}" {{ $loop->first ? 'checked' : '' }}>
                    <span>
                        <strong>{{ $address->recipient_name }} — {{ $address->phone }}</strong><br>
                        {{ $address->address_detail }}, {{ $address->ward }}, {{ $address->district }}, {{ $address->province }}
                        @if ($address->is_default)
                            <span class="checkout-option__badge">Mặc định</span>
                        @endif
                    </span>
                </label>
            @endforeach

            <a href="{{ route('addresses.create') }}" class="auth-link">+ Thêm địa chỉ mới</a>
        </div>

        <div class="checkout-section">
            <h2>Phương thức thanh toán</h2>

            <label class="checkout-option">
                <input type="radio" name="payment_method" value="cod" checked>
                <span><strong>Thanh toán khi nhận hàng (COD)</strong><br>Trả tiền mặt cho shipper khi nhận được hàng.</span>
            </label>

            <label class="checkout-option">
                <input type="radio" name="payment_method" value="vietqr">
                <span><strong>Chuyển khoản ngân hàng (VietQR)</strong><br>Quét mã QR, chuyển khoản trực tiếp từ app ngân hàng bất kỳ.</span>
            </label>

            {{-- 2 phương thức dưới đây sẽ mở ở các giai đoạn sau --}}
            <label class="checkout-option checkout-option--disabled">
                <input type="radio" name="payment_method" value="momo" disabled>
                <span><strong>Ví MoMo</strong><br>Sắp ra mắt.</span>
            </label>

            <label class="checkout-option checkout-option--disabled">
                <input type="radio" name="payment_method" value="vnpay" disabled>
                <span><strong>VNPay (thẻ nội địa + thẻ quốc tế)</strong><br>Sắp ra mắt.</span>
            </label>
        </div>

        <div class="checkout-section">
            <h2>Ghi chú (không bắt buộc)</h2>
            <textarea name="note" rows="3" class="form-input" placeholder="Ghi chú cho đơn hàng, VD giờ giao hàng thuận tiện..."></textarea>
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

        <div class="checkout-summary__row">
            <span>Tạm tính</span>
            <span>{{ number_format($subtotal, 0, ',', '.') }}₫</span>
        </div>
        <div class="checkout-summary__row">
            <span>Phí vận chuyển</span>
            <span>{{ number_format($shippingFee, 0, ',', '.') }}₫</span>
        </div>
        <div class="checkout-summary__row checkout-summary__row--total">
            <span>Tổng cộng</span>
            <span>{{ number_format($total, 0, ',', '.') }}₫</span>
        </div>

        <button type="submit" class="btn-primary">Đặt hàng</button>
    </div>
</form>
@endsection
