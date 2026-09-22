@extends('layouts.app')
@section('title', 'Sản phẩm yêu thích')
@section('content')
<div class="page-heading-row"><div><p class="home-section__eyebrow">MY UNIS</p><h1 class="page-title">Sản phẩm yêu thích</h1></div><span class="wishlist-count">{{ $wishlists->total() }} sản phẩm</span></div>
@if($wishlists->isEmpty())<div class="empty-state wishlist-empty"><strong>Danh sách yêu thích đang trống</strong><p>Lưu những sản phẩm bạn quan tâm để xem lại bất cứ lúc nào.</p><a href="{{ route('products.index') }}" class="btn-primary btn-primary--inline">Khám phá sản phẩm</a></div>@else
<div class="product-grid wishlist-grid">@foreach($wishlists as $wishlist) @php($product = $wishlist->product)<article class="wishlist-item">@include('products._product-card', ['product' => $product])<form method="POST" action="{{ route('wishlist.toggle', $product) }}" class="wishlist-remove-form">@csrf @method('DELETE')<button type="submit" title="Bỏ yêu thích">♥</button></form></article>@endforeach</div><div class="pagination-wrapper">{{ $wishlists->links() }}</div>@endif
@endsection
