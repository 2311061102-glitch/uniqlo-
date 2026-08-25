@extends('layouts.app')

@section('title', 'Đăng ký tài khoản')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <h1 class="auth-card__title">Tạo tài khoản</h1>

        <form method="POST" action="{{ route('register.store') }}" class="auth-form">
            @csrf

            <div class="form-group">
                <label for="name">Họ và tên</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}"
                       class="form-input @error('name') form-input--error @enderror" required>
                @error('name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       class="form-input @error('email') form-input--error @enderror" required>
                @error('email')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="phone">Số điện thoại</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                       class="form-input @error('phone') form-input--error @enderror"
                       placeholder="VD: 0912345678" required>
                @error('phone')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu</label>
                  <div class="password-field">
                      <input type="password" id="password" name="password"
                          class="form-input @error('password') form-input--error @enderror" required>
                      <button type="button" class="password-toggle" aria-label="Hiện mật khẩu" title="Hiện mật khẩu">&#128065;</button>
                  </div>
                  @include('auth._password-rules')
                @error('password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">Nhập lại mật khẩu</label>
                  <div class="password-field">
                      <input type="password" id="password_confirmation" name="password_confirmation"
                          class="form-input" required>
                      <button type="button" class="password-toggle" aria-label="Hiện mật khẩu" title="Hiện mật khẩu">&#128065;</button>
                  </div>
            </div>

            <button type="submit" class="btn-primary">Đăng ký</button>
        </form>

        <p class="auth-card__footer">
            Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a>
        </p>
    </div>
</div>
@endsection
