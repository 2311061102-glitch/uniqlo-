@extends('layouts.app')

@section('title', 'Sửa địa chỉ')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <h1 class="auth-card__title">Sửa địa chỉ</h1>

        <form method="POST" action="{{ route('addresses.update', $address) }}" class="auth-form">
            @csrf
            @method('PUT')
            @include('addresses._form')
            <button type="submit" class="btn-primary">Cập nhật</button>
        </form>

        <p class="auth-card__footer">
            <a href="{{ route('addresses.index') }}">Quay lại sổ địa chỉ</a>
        </p>
    </div>
</div>
@endsection
