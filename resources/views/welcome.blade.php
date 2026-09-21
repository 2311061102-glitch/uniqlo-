@extends('layouts.app')

@section('title', 'UNIQLO - LifeWear')

@section('content')
<div class="home-page">
    <section class="home-hero">
        <div class="home-hero__content">
            <p class="home-hero__eyebrow">Men's LifeWear collection 2026</p>
            <h1>Thời trang nam cho cuộc sống hiện đại</h1>
            <p>Thiết kế tối giản, thoải mái và bền bỉ cho mỗi ngày.</p>
            <a href="{{ route('products.index') }}" class="home-hero__cta">Khám phá bộ sưu tập</a>
        </div>
    </section>

    <section class="home-section">
        <div class="home-section__heading">
            <div><p class="home-section__eyebrow">UNIQLO MEN / LifeWear</p><h2>Những lựa chọn cho hôm nay</h2></div>
            <a href="{{ route('products.index') }}">Xem tất cả</a>
        </div>
        <div class="home-category-grid">
            <a href="{{ route('products.index') }}" class="home-category home-category--linen"><span>Đồ mặc hằng ngày</span><strong>Thoải mái từ sáng đến tối</strong></a>
            <a href="{{ route('products.index') }}" class="home-category home-category--outerwear"><span>Áo khoác nhẹ</span><strong>Sẵn sàng cho mọi chuyển động</strong></a>
            <a href="{{ route('products.index') }}" class="home-category home-category--essentials"><span>Essentials</span><strong>Những món đồ không thể thiếu</strong></a>
        </div>
    </section>
</div>
@endsection