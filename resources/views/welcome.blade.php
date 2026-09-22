@extends('layouts.app')

@section('title', 'UNIS Men - LifeWear')

@section('content')
<div class="home-page">
    <section class="home-hero">
        <div class="home-hero__content">
            <p class="home-hero__eyebrow">LifeWear / Fall Winter 2026</p>
            <h1>Made for every day.</h1>
            <p>Những thiết kế tối giản, thoải mái và bền bỉ cho nhịp sống hiện đại.</p>
            <a href="{{ route('products.index') }}" class="home-hero__cta">Khám phá bộ sưu tập <span>→</span></a>
        </div>
    </section>
    <section class="home-categories home-section">
        <div class="home-section__heading"><div><p class="home-section__eyebrow">UNIS MEN</p><h2>Mua sắm theo danh mục</h2></div><a href="{{ route('categories.index') }}">Xem tất cả →</a></div>
        <div class="home-category-grid">
            @forelse($homeCategories as $category)
                <a href="{{ route('products.category', $category) }}" class="home-category"><span>{{ $category->name }}</span><strong>{{ $category->products_count }} sản phẩm</strong></a>
            @empty
                <a href="{{ route('products.index') }}" class="home-category home-category--linen"><span>Essentials</span><strong>Những món đồ không thể thiếu</strong></a>
                <a href="{{ route('products.index') }}" class="home-category home-category--outerwear"><span>Outerwear</span><strong>Sẵn sàng cho mọi chuyển động</strong></a>
                <a href="{{ route('products.index') }}" class="home-category home-category--essentials"><span>LifeWear</span><strong>Thoải mái từ sáng đến tối</strong></a>
            @endforelse
        </div>
    </section>
    @if($featuredProducts->isNotEmpty())
        <section class="home-products home-section"><div class="home-section__heading"><div><p class="home-section__eyebrow">SELECTED FOR YOU</p><h2>Sản phẩm nổi bật</h2></div><a href="{{ route('products.index') }}">Xem tất cả →</a></div><div class="product-grid">@foreach($featuredProducts as $product) @include('products._product-card', ['product' => $product]) @endforeach</div></section>
    @endif
</div>
@endsection
