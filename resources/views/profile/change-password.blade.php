@extends('layouts.app')

@section('title', 'Đổi mật khẩu')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <h1 class="auth-card__title">Đổi mật khẩu</h1>

        <form method="POST" action="{{ route('password.change') }}" class="auth-form">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="current_password">Mật khẩu hiện tại</label>
                  <div class="password-field">
                      <input type="password" id="current_password" name="current_password"
                          class="form-input @error('current_password') form-input--error @enderror" required>
                      <button type="button" class="password-toggle" aria-label="Hiện mật khẩu" title="Hiện mật khẩu">&#128065;</button>
                  </div>
                @error('current_password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Mật khẩu mới</label>
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
                <label for="password_confirmation">Nhập lại mật khẩu mới</label>
                  <div class="password-field">
                      <input type="password" id="password_confirmation" name="password_confirmation"
                          class="form-input" required>
                      <button type="button" class="password-toggle" aria-label="Hiện mật khẩu" title="Hiện mật khẩu">&#128065;</button>
                  </div>
            </div>

            <button type="submit" class="btn-primary">Đổi mật khẩu</button>
        </form>

        <p class="auth-card__footer">
            <a href="{{ route('profile.edit') }}">Quay lại thông tin cá nhân</a>
        </p>
    </div>
</div>
@endsection
