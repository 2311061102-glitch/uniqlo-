@extends('layouts.app')

@section('title', 'Giỏ hàng')

@section('content')
<h1 class="page-title">Giỏ hàng của bạn</h1>

@if ($cartItems->isEmpty())
    <div class="empty-state">
        Giỏ hàng đang trống. <a href="{{ route('products.index') }}">Tiếp tục mua sắm</a>.
    </div>
@else
    <div class="cart-page">
        <div class="cart-list">
            @foreach ($cartItems as $item)
                <div class="cart-item">
                    @if ($item->variant->product->primary_image)
                        <img src="{{ $item->variant->product->primary_image->url }}"
                             alt="{{ $item->variant->product->name }}" class="cart-item__image">
                    @else
                        <div class="cart-item__image"></div>
                    @endif

                    <div class="cart-item__info">
                        <p class="cart-item__name">{{ $item->variant->product->name }}</p>
                        <p class="cart-item__variant">Size {{ $item->variant->size }} — {{ $item->variant->color }}</p>
                        <p class="cart-item__price">{{ number_format($item->variant->final_price, 0, ',', '.') }}₫</p>
                    </div>

                    <form method="POST" action="{{ route('cart.update', $item) }}" class="cart-item__qty-form">
                        @csrf
                        @method('PUT')
                        <input type="number" name="quantity" value="{{ $item->quantity }}" min="1"
                               max="{{ $item->variant->stock_quantity }}" class="quantity-input"
                               onchange="this.form.submit()">
                    </form>

                    <p class="cart-item__subtotal">{{ number_format($item->subtotal, 0, ',', '.') }}₫</p>

                    <form method="POST" action="{{ route('cart.destroy', $item) }}"
                          onsubmit="return confirm('Xóa sản phẩm này khỏi giỏ hàng?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="link-button link-button--danger">Xóa</button>
                    </form>
                </div>
            @endforeach
        </div>

        <div class="cart-summary">
            <div class="cart-summary__row">
                <span>Tạm tính</span>
                <strong>{{ number_format($subtotal, 0, ',', '.') }}₫</strong>
            </div>
            <p class="form-hint" style="margin-bottom: 16px;">Phí vận chuyển và mã giảm giá sẽ tính ở bước thanh toán.</p>

            {{-- route 'checkout.index' sẽ có ở Giai đoạn 3 --}}
            <a href="{{ Route::has('checkout.index') ? route('checkout.index') : '#' }}" class="btn-primary btn-primary--inline">
                Tiến hành thanh toán
            </a>
        </div>
    </div>
@endif
@endsection
