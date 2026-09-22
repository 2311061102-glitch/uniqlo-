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
             src="{{ $product->primary_image?->url ?? '' }}"
             alt="{{ $product->name }}" class="product-gallery__main">

        @if ($product->images->count() > 1)
            <div class="product-gallery__thumbs">
                @foreach ($product->images as $image)
                    <img src="{{ $image->url }}" alt="{{ $product->name }}"
                         class="product-gallery__thumb"
                         onclick="document.getElementById('main-image').src = this.src">
                @endforeach
            </div>
        @endif
    </div>

    <div class="product-info">
        <h1 class="product-info__name">{{ $product->name }}</h1>
        <p id="product-price" class="product-info__price">
            @if ($product->discount_percent > 0)
                <del>{{ number_format($product->base_price, 0, ',', '.') }}₫</del>
                {{ number_format($product->sale_price, 0, ',', '.') }}₫
                <small>-{{ $product->discount_percent }}%</small>
            @else
                {{ number_format($product->base_price, 0, ',', '.') }}₫
            @endif
        </p>

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

        <div class="product-actions">
            @auth
                <form method="POST" action="{{ route('wishlist.toggle', $product) }}">
                    @csrf
                    <button type="submit" id="wishlist-btn" class="btn-secondary">
                        {{ auth()->user()->hasInWishlist($product->id) ? '♥ Đã yêu thích' : '♡ Yêu thích' }}
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="btn-secondary">Đăng nhập để yêu thích</a>
            @endauth
            {{-- Nút thêm giỏ hàng thuộc phần Thành viên 3, tạm khóa ở đây --}}
            <button type="button" id="add-to-cart-btn" class="btn-primary" disabled>
                Thêm vào giỏ (phần Thành viên 3)
            </button>
        </div>

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
            <label for="rating">Đánh giá của bạn</label>
            <select id="rating" name="rating" class="form-input" required>
                <option value="">Chọn số sao</option>
                @for ($rating = 5; $rating >= 1; $rating--)
                    <option value="{{ $rating }}">{{ $rating }} sao</option>
                @endfor
            </select>
            <textarea name="comment" class="form-input" rows="3" placeholder="Chia sẻ cảm nhận của bạn"></textarea>
            <button class="btn-primary btn-primary--inline" type="submit">Gửi đánh giá</button>
        </form>
    @else
        <p class="form-hint"><a href="{{ route('login') }}">Đăng nhập</a> để viết đánh giá.</p>
    @endauth

    @forelse ($product->reviews as $review)
        <div class="review-item">
            <p class="review-item__header">
                <strong>{{ $review->user->name }}</strong> — {{ str_repeat('⭐', $review->rating) }}
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

    let selectedSize = null;
    let selectedColor = null;

    function formatPrice(number) {
        return new Intl.NumberFormat('vi-VN').format(number) + '₫';
    }

    // Đây là hàm GỌI THẬT lên server (không phải xử lý trong JS) mỗi khi
    // đã chọn đủ cả size và màu, để lấy đúng số lượng tồn kho hiện tại từ database.
    function checkStock() {
        if (!selectedSize || !selectedColor) {
            stockStatus.textContent = 'Vui lòng chọn size và màu.';
            stockStatus.className = 'stock-status';
            addToCartBtn.disabled = true;
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
                    return;
                }

                priceEl.textContent = formatPrice(data.price);

                if (data.in_stock) {
                    stockStatus.textContent = `Còn hàng (${data.stock_quantity} sản phẩm).`;
                    stockStatus.className = 'stock-status stock-status--in-stock';
                    addToCartBtn.disabled = false;
                } else {
                    stockStatus.textContent = 'Hết hàng.';
                    stockStatus.className = 'stock-status stock-status--out-of-stock';
                    addToCartBtn.disabled = true;
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
