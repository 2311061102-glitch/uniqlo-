@extends('layouts.app')

@section('title', 'UNIQLO Men | Phong cách tối giản mỗi ngày')

@section('content')
<section class="home-hero">
    <div class="home-hero__content">
        <p class="eyebrow">Bộ sưu tập mới 2026</p>
        <h1>Đơn giản để sống trọn từng ngày.</h1>
        <p>Những thiết kế nam dễ mặc, bền bỉ và thoải mái trong mọi chuyển động.</p>
        <a href="{{ route('products.index') }}" class="btn-primary btn-primary--inline">Khám phá sản phẩm</a>
    </div>
</section>

<section class="home-section">
    <div class="section-heading"><h2>Danh mục nổi bật</h2><a href="{{ route('categories.index') }}">Xem tất cả</a></div>
    <div class="category-grid">
        @foreach ($featuredCategories as $category)
            <a href="{{ route('products.category', $category) }}" class="category-tile">
                @if ($category->image)<img src="{{ asset('storage/'.$category->image) }}" alt="{{ $category->name }}">@endif
                <span>{{ $category->name }}</span><small>{{ $category->products_count }} sản phẩm</small>
            </a>
        @endforeach
    </div>
</section>

<section class="home-section">
    <div class="section-heading"><h2>Mới nhất</h2><a href="{{ route('products.index', ['sort' => '']) }}">Xem tất cả</a></div>
    <div class="product-grid">@foreach ($products as $product) @include('products._product-card') @endforeach</div>
</section>

<section class="home-section">
    <div class="section-heading"><h2>Bán chạy</h2><a href="{{ route('products.index', ['sort' => 'best_selling']) }}">Xem tất cả</a></div>
    <div class="product-grid">@foreach ($bestSellers as $product) @include('products._product-card') @endforeach</div>
</section>
@endsection
