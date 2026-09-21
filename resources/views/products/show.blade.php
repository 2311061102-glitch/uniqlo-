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
                            style="background: {{ $variant->color_hex ?? '#ccc' }}" title="{{ $variant->color }}">
                    </button>
                @endforeach
            </div>
        </div>

        <div id="stock-status" class="stock-status">Vui lòng chọn size và màu.</div>

        <div class="product-actions">
            {{-- Nút yêu thích sẽ hoạt động thật ở Giai đoạn 5 --}}
            <button type="button" id="wishlist-btn" class="btn-secondary" title="Sẽ hoạt động ở Giai đoạn 5">
                ♡ Yêu thích
            </button>

            {{-- Phần Thành viên 3: chọn số lượng + thêm vào giỏ hàng --}}
            <div class="add-to-cart-row">
                <input type="number" id="quantity-input" value="1" min="1" class="quantity-input">
                <button type="button" id="add-to-cart-btn" class="btn-primary" disabled>
                    Thêm vào giỏ
                </button>
            </div>
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

    {{-- Form viết đánh giá sẽ làm ở Giai đoạn 4, hiện tại chỉ xem được, chưa viết được --}}

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
    const quantityInput = document.getElementById('quantity-input');

    let selectedSize = null;
    let selectedColor = null;
    let selectedVariantId = null;

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
            selectedVariantId = null;
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
                    selectedVariantId = null;
                    return;
                }

                priceEl.textContent = formatPrice(data.price);

                if (data.in_stock) {
                    stockStatus.textContent = `Còn hàng (${data.stock_quantity} sản phẩm).`;
                    stockStatus.className = 'stock-status stock-status--in-stock';
                    addToCartBtn.disabled = false;
                    selectedVariantId = data.variant_id;

                    // Không cho chọn số lượng vượt quá tồn kho hiện có
                    quantityInput.max = data.stock_quantity;
                    if (parseInt(quantityInput.value, 10) > data.stock_quantity) {
                        quantityInput.value = data.stock_quantity;
                    }
                } else {
                    stockStatus.textContent = 'Hết hàng.';
                    stockStatus.className = 'stock-status stock-status--out-of-stock';
                    addToCartBtn.disabled = true;
                    selectedVariantId = null;
                }
            })
            .catch(() => {
                stockStatus.textContent = 'Có lỗi khi kiểm tra tồn kho, vui lòng thử lại.';
                selectedVariantId = null;
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

    // ===== Phần Thành viên 3: xử lý bấm "Thêm vào giỏ" =====
    addToCartBtn.addEventListener('click', () => {
        if (!selectedVariantId) {
            alert('Vui lòng chọn size và màu trước khi thêm vào giỏ.');
            return;
        }

        const quantity = parseInt(quantityInput.value, 10) || 1;

        addToCartBtn.disabled = true;
        addToCartBtn.textContent = 'Đang thêm...';

        fetch(`/gio-hang/them/${selectedVariantId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ quantity }),
        })
            .then(response => response.json().then(data => ({ status: response.status, data })))
            .then(({ status, data }) => {
                addToCartBtn.disabled = false;
                addToCartBtn.textContent = 'Thêm vào giỏ';

                if (status !== 200 || !data.success) {
                    alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                    return;
                }

                alert('Đã thêm vào giỏ hàng!');
                window.location.href = '{{ route("cart.index") }}';
            })
            .catch(() => {
                addToCartBtn.disabled = false;
                addToCartBtn.textContent = 'Thêm vào giỏ';
                alert('Có lỗi khi kết nối tới server, vui lòng thử lại.');
            });
    });
})();
</script>
@endpush
@endsection