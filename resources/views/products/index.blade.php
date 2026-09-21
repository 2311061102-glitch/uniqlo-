@extends('layouts.app')

@section('title', $currentCategory ? $currentCategory->name : 'Tất cả sản phẩm')

@section('content')
<h1 class="page-title">{{ $currentCategory ? $currentCategory->name : 'Tất cả sản phẩm nam' }}</h1>

<div class="product-page">
    <aside class="filter-sidebar">
        <form method="GET">
            <div class="filter-group">
                <p class="filter-group__title">Danh mục</p>
                <label><a href="{{ route('products.index') }}">Tất cả</a></label>
                @foreach ($categories as $cat)
                    <label>
                        <a href="{{ route('products.category', $cat) }}"
                           style="{{ $currentCategory && $currentCategory->id === $cat->id ? 'font-weight:bold' : '' }}">
                            {{ $cat->name }}
                        </a>
                    </label>
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
                <div class="filter-price-inputs">
                    <input type="number" name="min_price" placeholder="Từ" value="{{ request('min_price') }}">
                    <input type="number" name="max_price" placeholder="Đến" value="{{ request('max_price') }}">
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
                    <option value="best_selling" @selected(request('sort') === 'best_selling')>Bán chạy</option>
                    <option value="price_asc" @selected(request('sort') === 'price_asc')>Giá tăng dần</option>
                    <option value="price_desc" @selected(request('sort') === 'price_desc')>Giá giảm dần</option>
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
@endsection
