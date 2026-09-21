{{-- File này được @include vào trang danh sách, trang chủ, trang tìm kiếm... --}}
<a href="{{ route('products.show', $product) }}" class="product-card">
    @if ($product->primary_image)
        <img src="{{ $product->primary_image->url }}"
             alt="{{ $product->name }}" class="product-card__image">
    @else
        <div class="product-card__image"></div>
    @endif

    <p class="product-card__name">{{ $product->name }}</p>
    <p class="product-card__price">{{ number_format($product->base_price, 0, ',', '.') }}₫</p>

    @if ($product->average_rating)
        <p class="product-card__rating">⭐ {{ $product->average_rating }} ({{ $product->reviews->count() }})</p>
    @endif
</a>
