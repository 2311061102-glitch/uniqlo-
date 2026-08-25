@extends('layouts.app')

@section('title', $product->name)

@section('content')
<nav class="breadcrumb">
    <a href="{{ route('home') }}">Trang chủ</a> /
    <a href="{{ route('products.category', $product->category) }}">{{ $product->category->name }}</a> /
    <span>{{ $product->name }}</span>
</nav>

<div class="product-detail">
    <div class="product-gallery">
        <img id="main-image"
             src="{{ $product->primary_image ? asset('storage/'.$product->primary_image->image_path) : '' }}"
             alt="{{ $product->name }}" class="product-gallery__main">

        @if ($product->images->count() > 1)
            <div class="product-gallery__thumbs">
                @foreach ($product->images as $image)
                    <img src="{{ asset('storage/'.$image->image_path) }}" alt="{{ $product->name }}"
                         class="product-gallery__thumb"
                         onclick="document.getElementById('main-image').src = this.src">
                @endforeach
            </div>
        @endif
    </div>

    <div class="product-info">
        <h1 class="product-info__name">{{ $product->name }}</h1>
        <p id="product-price" class="product-info__price">{{ number_format($product->base_price, 0, ',', '.') }}₫</p>

        @if ($product->average_rating)
            <p class="product-info__rating">⭐ {{ $product->average_rating }}/5 ({{ $product->reviews->count() }} đánh giá)</p>
        @endif

        <p class="product-info__material">Chất liệu: {{ $product->material ?? 'Đang cập nhật' }}</p>

        <div class="variant-selector">
            <p class="variant-selector__label">Kích cỡ</p>
            <div class="variant-selector__options" id="size-options">
                @foreach ($sizes as $size)
                    <button type="button" class="variant-option" data-size="{{ $size }}">{{ $size }}</button>
                @endforeach
            </div>
        </div>

        <div class="variant-selector">
            <p class="variant-selector__label">Màu sắc</p>
            <div class="variant-selector__options" id="color-options">
                @foreach ($colors as $variant)
                    <button type="button" class="color-swatch" data-color="{{ $variant->color }}"
                            style="background: {{ $variant->color_hex ?? '#ccc' }}" title="{{ $variant->color }}"></button>
                @endforeach
            </div>
        </div>

        <div id="stock-status" class="stock-status">Vui lòng chọn size và màu.</div>

        {{-- Form thêm vào giỏ — chỉ submit được khi đã chọn đủ size+màu VÀ còn hàng --}}
        <form method="POST" action="{{ route('cart.store') }}" id="add-to-cart-form">
            @csrf
            <input type="hidden" name="product_variant_id" id="selected-variant-id" value="">

            <div class="product-actions">
                @auth
                    @if ($isWishlisted ?? false)
                        <button type="button" class="btn-secondary btn-secondary--active" title="Chức năng wishlist ở Giai đoạn 5 Thành viên 2">♥</button>
                    @else
                        <button type="button" class="btn-secondary" title="Chức năng wishlist ở Giai đoạn 5 Thành viên 2">♡</button>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn-secondary">♡</a>
                @endauth

                <input type="number" name="quantity" id="cart-quantity" value="1" min="1" class="quantity-input">

                <button type="submit" id="add-to-cart-btn" class="btn-primary" disabled>
                    Thêm vào giỏ hàng
                </button>
            </div>
        </form>

        <div class="product-description">
            <h2>Mô tả sản phẩm</h2>
            <p>{{ $product->description ?? 'Đang cập nhật mô tả.' }}</p>
        </div>

        <div class="size-chart">
            <h2>Bảng size</h2>
            <table>
                <thead>
                    <tr><th>Size</th><th>Ngực (cm)</th><th>Dài áo (cm)</th></tr>
                </thead>
                <tbody>
                    <tr><td>S</td><td>92-96</td><td>68</td></tr>
                    <tr><td>M</td><td>97-101</td><td>70</td></tr>
                    <tr><td>L</td><td>102-106</td><td>72</td></tr>
                    <tr><td>XL</td><td>107-111</td><td>74</td></tr>
                    <tr><td>XXL</td><td>112-116</td><td>76</td></tr>
                </tbody>
            </table>
            <p class="form-hint">* Bảng size tham khảo chung, sẽ khác nhau theo từng mẫu sản phẩm thật.</p>
        </div>
    </div>
</div>

<div class="product-reviews">
    <h2>Đánh giá sản phẩm ({{ $product->reviews->count() }})</h2>

    @auth
        <form method="POST" action="{{ route('reviews.store', $product) }}" class="review-form">
            @csrf
            <div class="star-rating">
                @for ($i = 5; $i >= 1; $i--)
                    <input type="radio" id="star{{ $i }}" name="rating" value="{{ $i }}"
                           {{ old('rating', $userReview->rating ?? null) == $i ? 'checked' : '' }}>
                    <label for="star{{ $i }}">★</label>
                @endfor
            </div>
            @error('rating')
                <p class="form-error">{{ $message }}</p>
            @enderror

            <textarea name="comment" rows="3" class="form-input" placeholder="Nhận xét của bạn về sản phẩm (không bắt buộc)...">{{ old('comment', $userReview->comment ?? '') }}</textarea>
            @error('comment')
                <p class="form-error">{{ $message }}</p>
            @enderror

            <button type="submit" class="btn-primary btn-primary--inline">
                {{ $userReview ? 'Cập nhật đánh giá' : 'Gửi đánh giá' }}
            </button>
        </form>
    @else
        <p class="form-hint">
            <a href="{{ route('login') }}">Đăng nhập</a> để viết đánh giá cho sản phẩm này.
        </p>
    @endauth

    @forelse ($product->reviews as $review)
        <div class="review-item">
            <p class="review-item__header">
                <strong>{{ $review->user->name }}</strong> — {{ str_repeat('⭐', $review->rating) }}

                @auth
                    @if ($review->user_id === auth()->id())
                        <form method="POST" action="{{ route('reviews.destroy', $review) }}"
                              style="display:inline" onsubmit="return confirm('Xóa đánh giá của bạn?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="link-button link-button--danger">Xóa</button>
                        </form>
                    @endif
                @endauth
            </p>
            @if ($review->comment)
                <p class="review-item__comment">{{ $review->comment }}</p>
            @endif
        </div>
    @empty
        <p class="form-hint">Chưa có đánh giá nào cho sản phẩm này.</p>
    @endforelse
</div>

@push('scripts')
<script>
(function () {
    const sizeButtons = document.querySelectorAll('#size-options .variant-option');
    const colorButtons = document.querySelectorAll('#color-options .color-swatch');
    const stockStatus = document.getElementById('stock-status');
    const priceEl = document.getElementById('product-price');
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    const selectedVariantInput = document.getElementById('selected-variant-id');

    let selectedSize = null;
    let selectedColor = null;

    function formatPrice(number) {
        return new Intl.NumberFormat('vi-VN').format(number) + '₫';
    }

    function checkStock() {
        if (!selectedSize || !selectedColor) {
            stockStatus.textContent = 'Vui lòng chọn size và màu.';
            stockStatus.className = 'stock-status';
            addToCartBtn.disabled = true;
            selectedVariantInput.value = '';
            return;
        }

        stockStatus.textContent = 'Đang kiểm tra tồn kho...';

        const url = `{{ route('products.checkStock', $product) }}?size=${encodeURIComponent(selectedSize)}&color=${encodeURIComponent(selectedColor)}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (!data.found) {
                    stockStatus.textContent = 'Không có sẵn tổ hợp size + màu này.';
                    stockStatus.className = 'stock-status stock-status--out-of-stock';
                    addToCartBtn.disabled = true;
                    selectedVariantInput.value = '';
                    return;
                }

                priceEl.textContent = formatPrice(data.price);

                if (data.in_stock) {
                    stockStatus.textContent = `Còn hàng (${data.stock_quantity} sản phẩm).`;
                    stockStatus.className = 'stock-status stock-status--in-stock';
                    addToCartBtn.disabled = false;
                    // Lưu ID biến thể vào ô ẩn -> đây là giá trị THẬT được gửi đi khi bấm "Thêm vào giỏ"
                    selectedVariantInput.value = data.variant_id;
                } else {
                    stockStatus.textContent = 'Hết hàng.';
                    stockStatus.className = 'stock-status stock-status--out-of-stock';
                    addToCartBtn.disabled = true;
                    selectedVariantInput.value = '';
                }
            })
            .catch(() => {
                stockStatus.textContent = 'Có lỗi khi kiểm tra tồn kho, vui lòng thử lại.';
            });
    }

    sizeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            sizeButtons.forEach(b => b.classList.remove('variant-option--selected'));
            btn.classList.add('variant-option--selected');
            selectedSize = btn.dataset.size;
            checkStock();
        });
    });

    colorButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            colorButtons.forEach(b => b.classList.remove('color-swatch--selected'));
            btn.classList.add('color-swatch--selected');
            selectedColor = btn.dataset.color;
            checkStock();
        });
    });
})();
</script>
@endpush
@endsection
