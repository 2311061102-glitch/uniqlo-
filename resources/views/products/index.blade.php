@extends('layouts.app')

@section('title', $isSaleCategory ? 'Sản phẩm khuyến mãi' : ($currentCategory ? $currentCategory->name : 'Tất cả sản phẩm'))

@section('content')
<h1 class="page-title">{{ $isSaleCategory ? 'Sản phẩm khuyến mãi' : ($currentCategory ? $currentCategory->name : 'Tất cả sản phẩm nam') }}</h1>

<div class="product-page">
    <aside class="filter-sidebar">
        <form method="GET">
            <div class="filter-group">
                <p class="filter-group__title">Danh mục</p>
                <label><a href="{{ route('products.index') }}">Tất cả</a></label>
                <label>
                    <a href="{{ route('products.sale') }}"
                       style="{{ $isSaleCategory ? 'font-weight:bold; color:#d00' : '' }}">
                        Sản phẩm khuyến mãi
                    </a>
                </label>
                @foreach ($categories as $cat)
                    @if ($cat->children->isNotEmpty())
                        <details class="category-filter-group" @if ($currentCategory && ($currentCategory->id === $cat->id || $currentCategory->parent_id === $cat->id)) open @endif>
                            <summary>{{ $cat->name }}</summary>
                            <div class="category-filter-group__children">
                                @foreach ($cat->children as $child)
                                    <a href="{{ route('products.category', $child) }}"
                                       style="{{ $currentCategory && $currentCategory->id === $child->id ? 'font-weight:bold' : '' }}">
                                        {{ $child->name }}
                                    </a>
                                @endforeach
                            </div>
                        </details>
                    @else
                        <label>
                            <a href="{{ route('products.category', $cat) }}">{{ $cat->name }}</a>
                        </label>
                    @endif
                @endforeach
            </div>

            <div class="filter-group">
                <p class="filter-group__title">Kích cỡ</p>
                @foreach ($sizes as $size)
                    <label>
                        <input type="radio" name="size" value="{{ $size }}"
                               {{ request('size') === $size ? 'checked' : '' }}
                               onchange="this.form.submit()">
                        {{ $size }}
                    </label>
                @endforeach
            </div>

            <div class="filter-group">
                <p class="filter-group__title">Màu sắc</p>
                <div class="color-swatch-list">
                    @foreach ($colors as $color)
                        <label title="{{ $color->color }}">
                            <input type="radio" name="color" value="{{ $color->color }}"
                                   style="display:none" onchange="this.form.submit()"
                                   {{ request('color') === $color->color ? 'checked' : '' }}>
                            <span class="color-swatch {{ request('color') === $color->color ? 'color-swatch--selected' : '' }}"
                                  style="background: {{ $color->color_hex ?? '#ccc' }}"></span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="filter-group">
                <p class="filter-group__title">Khoảng giá (VNĐ)</p>
                <div class="price-range" data-price-range>
                    <div class="price-range__values">
                        <output data-price-min>0₫</output>
                        <output data-price-max>3,000,000₫</output>
                    </div>
                    <div class="price-range__track">
                        <div class="price-range__selected" data-price-selected></div>
                        <input type="range" min="0" max="3000000" step="50000"
                               value="{{ request('min_price', 0) }}" data-price-min-slider aria-label="Giá tối thiểu">
                        <input type="range" min="0" max="3000000" step="50000"
                               value="{{ request('max_price', 3000000) }}" data-price-max-slider aria-label="Giá tối đa">
                    </div>
                    <div class="price-range__bounds">
                        <span>0₫</span>
                        <span>3,000,000₫</span>
                    </div>
                    <input type="hidden" name="min_price" value="{{ request('min_price', 0) }}" data-price-min-input>
                    <input type="hidden" name="max_price" value="{{ request('max_price', 3000000) }}" data-price-max-input>
                </div>
            </div>

            <div class="filter-group">
                <p class="filter-group__title">Chất liệu</p>
                <input type="text" name="material" placeholder="VD: Cotton" value="{{ request('material') }}" class="form-input">
            </div>

            <button type="submit" class="btn-primary btn-primary--inline">Lọc</button>
        </form>
    </aside>

    <div class="product-main">
        <div class="product-toolbar">
            <p>{{ $products->total() }} sản phẩm</p>

            <form method="GET" id="sort-form">
                {{-- Giữ lại toàn bộ filter đang chọn khi đổi cách sắp xếp --}}
                @foreach (request()->except(['sort', 'page']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach

                <select name="sort" onchange="document.getElementById('sort-form').submit()">
                    <option value="">Mới nhất</option>
                    <option value="featured" @selected(request('sort') === 'featured')>Sản phẩm nổi bật</option>
                    <option value="sale" @selected(request('sort') === 'sale')>Đang giảm giá</option>
                    <option value="price_asc" @selected(request('sort') === 'price_asc')>Giá: Tăng dần</option>
                    <option value="price_desc" @selected(request('sort') === 'price_desc')>Giá: Giảm dần</option>
                    <option value="name_asc" @selected(request('sort') === 'name_asc')>Tên: A-Z</option>
                    <option value="name_desc" @selected(request('sort') === 'name_desc')>Tên: Z-A</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Cũ nhất</option>
                    <option value="best_selling" @selected(request('sort') === 'best_selling')>Bán chạy nhất</option>
                    <option value="stock_desc" @selected(request('sort') === 'stock_desc')>Tồn kho giảm dần</option>
                </select>
            </form>
        </div>

        @if ($products->isEmpty())
            <div class="empty-state">Không tìm thấy sản phẩm phù hợp với bộ lọc.</div>
        @else
            <div class="product-grid">
                @foreach ($products as $product)
                    @include('products._product-card', ['product' => $product])
                @endforeach
            </div>

            <div class="pagination-wrapper">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
(function () {
    const range = document.querySelector('[data-price-range]');
    if (!range) return;

    const minSlider = range.querySelector('[data-price-min-slider]');
    const maxSlider = range.querySelector('[data-price-max-slider]');
    const minInput = range.querySelector('[data-price-min-input]');
    const maxInput = range.querySelector('[data-price-max-input]');
    const minOutput = range.querySelector('[data-price-min]');
    const maxOutput = range.querySelector('[data-price-max]');
    const selected = range.querySelector('[data-price-selected]');
    const maximum = Number(maxSlider.max);

    function formatPrice(value) {
        return new Intl.NumberFormat('vi-VN').format(value) + '₫';
    }

    function updateRange() {
        let min = Number(minSlider.value);
        let max = Number(maxSlider.value);

        if (min > max) {
            [min, max] = [max, min];
            minSlider.value = min;
            maxSlider.value = max;
        }

        minInput.value = min;
        maxInput.value = max;
        minOutput.textContent = formatPrice(min);
        maxOutput.textContent = formatPrice(max);
        selected.style.left = `${(min / maximum) * 100}%`;
        selected.style.right = `${100 - (max / maximum) * 100}%`;
    }

    minSlider.addEventListener('input', updateRange);
    maxSlider.addEventListener('input', updateRange);
    updateRange();
})();
</script>
@endpush
@endsection
