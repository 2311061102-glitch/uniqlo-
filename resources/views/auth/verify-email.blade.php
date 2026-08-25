@extends('layouts.app')

@section('title', 'Xác thực email')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <h1 class="auth-card__title">Xác thực email của bạn</h1>

        <p class="form-hint" style="margin-bottom: 16px;">
            Chúng tôi đã gửi 1 email xác thực tới <strong>{{ auth()->user()->email }}</strong>.
            Vui lòng mở email và bấm vào link xác thực trước khi sử dụng đầy đủ tính năng.
        </p>

        @if (session('success'))
            <p class="form-hint" style="color: #1a7f4a; margin-bottom: 16px;">{{ session('success') }}</p>
        @endif

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn-primary">Gửi lại email xác thực</button>
        </form>

        <p class="auth-card__footer">
            <a href="{{ route('home') }}">Quay lại trang chủ</a>
        </p>
    </div>
</div>
@endsection
