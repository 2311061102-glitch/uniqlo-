@extends('layouts.app')

@section('title', 'Hệ thống cửa hàng')

@section('content')
<div class="stores-page">
    <div class="stores-page__heading">
        <div>
            <p class="cart-eyebrow">UNIS MEN</p>
            <h1>Hệ thống cửa hàng</h1>
            <p>30 chi nhánh trên toàn quốc, sẵn sàng phục vụ bạn mỗi ngày.</p>
        </div>
        <span class="stores-page__count">{{ count($branches) }} chi nhánh</span>
    </div>

    <div class="stores-grid">
        @foreach ($branches as $branch)
            <article class="store-card">
                <div class="store-card__top"><span>{{ $branch['code'] }}</span><span class="store-card__status">Đang hoạt động</span></div>
                <h2>{{ $branch['name'] }}</h2>
                <p>{{ $branch['address'] }}</p>
                <a href="https://www.google.com/maps/search/?api=1&amp;query={{ $branch['latitude'] }},{{ $branch['longitude'] }}" target="_blank" rel="noopener" class="store-card__map">Xem trên bản đồ <span>↗</span></a>
            </article>
        @endforeach
    </div>
</div>
@endsection
