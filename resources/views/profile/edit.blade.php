@extends('layouts.app')

@section('title', 'Thông tin cá nhân')

@section('content')
<div class="auth-page">
    <div class="profile-stack">
    <div class="auth-card">
        <h1 class="auth-card__title">Thông tin cá nhân</h1>

        <div class="profile-avatar">
            @if ($user->avatar)
                <img src="{{ asset('storage/'.$user->avatar) }}" alt="Avatar" class="profile-avatar__img">
            @else
                <div class="profile-avatar__placeholder">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            @endif
        </div>

        <form method="POST" action="{{ route('profile.update') }}" class="auth-form" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="avatar">Đổi ảnh đại diện</label>
                <input type="file" id="avatar" name="avatar" class="form-input" accept="image/*">
                @error('avatar')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="name">Họ và tên</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                       class="form-input @error('name') form-input--error @enderror" required>
                @error('name')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" value="{{ $user->email }}" class="form-input" disabled>
                <p class="form-hint">Không thể đổi email sau khi đăng ký.</p>
            </div>

            <div class="form-group">
                <label for="phone">Số điện thoại</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                       class="form-input @error('phone') form-input--error @enderror" required>
                @error('phone')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-primary">Lưu thay đổi</button>
        </form>

        <p class="auth-card__footer">
            <a href="{{ route('password.edit') }}">Đổi mật khẩu</a>
        </p>
    </div>

    {{-- ===== Vùng nguy hiểm: xóa tài khoản ===== --}}
    <div class="auth-card danger-zone">
        <h2 class="danger-zone__title">Xóa tài khoản</h2>
        <p class="form-hint" style="margin-bottom: 16px;">
            Hành động này không thể hoàn tác. Bạn sẽ không thể đăng nhập lại bằng tài khoản này.
        </p>

        <form method="POST" action="{{ route('profile.destroy') }}" class="auth-form"
              onsubmit="return confirm('Bạn chắc chắn muốn xóa tài khoản? Hành động này không thể hoàn tác.');">
            @csrf
            @method('DELETE')

            <div class="form-group">
                <label for="current_password_delete">Nhập mật khẩu để xác nhận</label>
                <input type="password" id="current_password_delete" name="current_password"
                       class="form-input @error('current_password') form-input--error @enderror" required>
                @error('current_password')
                    <p class="form-error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn-danger">Xóa tài khoản vĩnh viễn</button>
        </form>
    </div>
    </div>
</div>
@endsection
