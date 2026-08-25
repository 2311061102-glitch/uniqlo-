@extends('layouts.app')

@section('title', 'Đăng nhập')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <h1 class="auth-card__title">Đăng nhập</h1>

        @error('email')
            <p class="form-error form-error--top">{{ $message }}</p>
        @enderror

        <form method="POST" action="{{ route('login.store') }}" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="email">Email hoặc số điện thoại</label>
                <input type="text" id="email" name="email" value="{{ old('email') }}"
                       class="form-input" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <div class="password-field">
                    <input type="password" id="password" name="password" class="form-input" required>
                    <button type="button" class="password-toggle" aria-label="Hiện mật khẩu" title="Hiện mật khẩu">&#128065;</button>
                </div>
            </div>

            <div class="form-group form-group--inline">
                <label>
                    <input type="checkbox" name="remember"> Ghi nhớ đăng nhập
                </label>
                <a href="{{ route('password.request') }}" class="auth-link">Quên mật khẩu?</a>
            </div>

            <button type="submit" class="btn-primary">Đăng nhập</button>
        </form>

        <a href="{{ route('login.google') }}" class="btn-secondary" style="display:block; text-align:center; margin-top:12px; text-decoration:none;">Đăng nhập bằng Google</a>

        <p class="auth-card__footer">
            Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký ngay</a>
        </p>
    </div>
</div>
@endsection
