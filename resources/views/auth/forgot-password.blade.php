@extends('layouts.app')

@section('title', 'Quên mật khẩu')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <h1 class="auth-card__title">Quên mật khẩu</h1>
        <p class="form-hint" style="margin-bottom: 16px;">
            Nhập email đã đăng ký, chúng tôi sẽ gửi link đặt lại mật khẩu cho bạn.
        </p>

        @error('email')
            <p class="form-error form-error--top">{{ $message }}</p>
        @enderror

        <form method="POST" action="{{ route('password.email') }}" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       class="form-input" required autofocus>
            </div>

            <button type="submit" class="btn-primary">Gửi link đặt lại mật khẩu</button>
        </form>

        <p class="auth-card__footer">
            <a href="{{ route('login') }}">Quay lại đăng nhập</a>
        </p>
    </div>
</div>
@endsection
