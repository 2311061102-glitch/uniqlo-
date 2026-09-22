@extends('layouts.app')
@section('title', 'Giỏ hàng')
@section('content')
<div class="cart-shell">
    <div class="cart-pick-banner"><div class="cart-pick-banner__mark">↗</div><div><strong>ORDER &amp; PICK</strong><span>Đặt hàng online — nhận hàng nhanh chóng tại cửa hàng.</span></div><a href="{{ route('products.index') }}">Tìm hiểu thêm →</a></div>
    <div class="cart-heading"><div><p class="cart-eyebrow">MY UNIS</p><h1>Giỏ hàng</h1></div><span>{{ $cartItems->sum('quantity') }} sản phẩm</span></div>
    @if ($cartItems->isEmpty())
        <div class="cart-empty-state"><div class="cart-empty-state__icon">□</div><h2>Giỏ hàng đang trống</h2><p>Hãy khám phá các sản phẩm LifeWear và thêm món đồ bạn yêu thích.</p><a href="{{ route('products.index') }}" class="cart-primary-button">Tiếp tục mua sắm <span>→</span></a></div>
    @else
        <div class="cart-page">
            <div class="cart-list">
                @foreach ($cartItems as $item)
                    <article class="cart-item">
                        @if ($item->variant->product->primary_image)<img src="{{ $item->variant->product->primary_image->url }}" alt="{{ $item->variant->product->name }}" class="cart-item__image">@else<div class="cart-item__image"></div>@endif
                        <div class="cart-item__info"><a href="{{ route('products.show', $item->variant->product) }}" class="cart-item__name">{{ $item->variant->product->name }}</a><p class="cart-item__variant">Màu: {{ $item->variant->color }} · Size: {{ $item->variant->size }}</p><p class="cart-item__price">{{ number_format($item->variant->final_price, 0, ',', '.') }}₫</p></div>
                        <form method="POST" action="{{ route('cart.update', $item) }}" class="cart-item__qty-form">@csrf @method('PUT')<button type="button" onclick="this.nextElementSibling.stepDown();this.form.submit()" aria-label="Giảm">−</button><input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->variant->stock_quantity }}" aria-label="Số lượng" onchange="this.form.submit()"><button type="button" onclick="this.previousElementSibling.stepUp();this.form.submit()" aria-label="Tăng">+</button></form>
                        <p class="cart-item__subtotal">{{ number_format($item->subtotal, 0, ',', '.') }}₫</p>
                        <form method="POST" action="{{ route('cart.destroy', $item) }}" onsubmit="return confirm('Xóa sản phẩm này khỏi giỏ hàng?')">@csrf @method('DELETE')<button type="submit" class="cart-item__remove">Xóa</button></form>
                    </article>
                @endforeach
            </div>
            <aside class="cart-summary"><div class="cart-summary__title"><h2>Tóm tắt đơn hàng</h2><span>{{ $cartItems->sum('quantity') }} món</span></div><div class="cart-summary__row"><span>Tạm tính</span><strong>{{ number_format($subtotal, 0, ',', '.') }}₫</strong></div><p class="cart-summary__note">Phí vận chuyển và mã giảm giá sẽ được tính ở bước thanh toán.</p><div class="cart-summary__total"><span>Tổng cộng</span><strong>{{ number_format($subtotal, 0, ',', '.') }}₫</strong></div><a href="{{ route('checkout.index') }}" class="cart-primary-button">Tiến hành thanh toán <span>→</span></a><p class="cart-summary__secure">⌁ Thanh toán an toàn · Bảo mật thông tin</p></aside>
        </div>
    @endif
</div>
@endsection
