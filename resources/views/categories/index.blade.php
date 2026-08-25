@extends('layouts.app')

@section('title', 'Danh mục sản phẩm')

@section('content')
<h1 class="page-title">Danh mục sản phẩm</h1>

<div class="category-grid">
    @foreach ($categories as $category)
        <a href="{{ route('products.category', $category) }}" class="category-card">
            <p class="category-card__name">{{ $category->name }}</p>
        </a>
    @endforeach
</div>
@endsection
