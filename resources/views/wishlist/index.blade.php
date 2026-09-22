@extends('layouts.app')

@section('title', 'Danh sách yêu thích')

@section('content')
<div class="section-heading">
    <h1 class="page-title">Danh sách yêu thích</h1>
    <span>{{ $products->total() }} sản phẩm</span>
</div>

@if ($products->isEmpty())
    <div class="empty-state">
        <h2>Danh sách của bạn đang trống</h2>
        <p>Hãy lưu những món đồ bạn yêu thích để xem lại sau.</p>
        <a href="{{ route('products.index') }}" class="btn-primary btn-primary--inline">Khám phá sản phẩm</a>
    </div>
@else
    <div class="product-grid">
        @foreach ($products as $wishlist)
            @include('products._product-card', ['product' => $wishlist->product])
        @endforeach
    </div>
    <div class="pagination-wrapper">{{ $products->links() }}</div>
@endif
@endsection
