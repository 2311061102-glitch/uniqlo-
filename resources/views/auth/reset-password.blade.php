@extends('layouts.app')

@section('title', 'Đặt lại mật khẩu')

@section('content')
<div class="auth-page">
    <div class="auth-card">
        <h1 class="auth-card__title">Đặt mật khẩu mới</h1>

        @error('email')
            <p class="form-error form-error--top">{{ $message }}</p>
        @enderror

        <form method="POST" action="{{ route('password.update') }}" class="auth-form">
            @csrf

            {{-- token và email được gửi ngầm, người dùng không cần nhìn thấy hay tự nhập --}}
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <div class="form-group">
                <label for="password">Mật khẩu mới</label>
                <input type="password" id="password" name="password" class="form-input" required autofocus>
                <p class="form-hint">Ít nhất 8 ký tự, có chữ hoa, chữ thường và số.</p>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Nhập lại mật khẩu mới</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                       class="form-input" required>
            </div>

            <button type="submit" class="btn-primary">Đặt lại mật khẩu</button>
        </form>
    </div>
</div>
@endsection
