@extends('layouts.app')

@section('title', 'Danh mục sản phẩm')

@section('content')
<h1 class="page-title">Danh mục sản phẩm</h1>

<div class="category-grid">
    @foreach ($categories as $category)
        @if ($category->children->isNotEmpty())
            <details class="category-card category-card--group">
                <summary class="category-card__name">{{ $category->name }}</summary>
                <div class="category-card__children">
                    @foreach ($category->children as $child)
                        <a href="{{ route('products.category', $child) }}">{{ $child->name }}</a>
                    @endforeach
                </div>
            </details>
        @else
            <a href="{{ route('products.category', $category) }}" class="category-card">
                <p class="category-card__name">{{ $category->name }}</p>
            </a>
        @endif
    @endforeach
</div>
@endsection
