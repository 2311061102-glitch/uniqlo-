@extends('layouts.app')

@section('title', 'Thêm địa chỉ mới')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <h1 class="auth-card__title">Thêm địa chỉ mới</h1>

        <form method="POST" action="{{ route('addresses.store') }}" class="auth-form">
            @csrf
            @include('addresses._form')
            <button type="submit" class="btn-primary">Lưu địa chỉ</button>
        </form>

        <p class="auth-card__footer">
            <a href="{{ route('addresses.index') }}">Quay lại sổ địa chỉ</a>
        </p>
    </div>
</div>
@endsection
