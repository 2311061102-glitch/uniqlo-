@extends('layouts.app')

@section('title', 'Sổ địa chỉ')

@section('content')
<div class="address-page">
    <div class="address-page__header">
        <h1 class="auth-card__title">Sổ địa chỉ giao hàng</h1>
        <a href="{{ route('addresses.create') }}" class="btn-primary btn-primary--inline">+ Thêm địa chỉ mới</a>
    </div>

    @if ($addresses->isEmpty())
        <p class="form-hint">Bạn chưa có địa chỉ nào. Bấm "Thêm địa chỉ mới" để thêm.</p>
    @endif

    <div class="address-list">
        @foreach ($addresses as $address)
            <div class="address-card">
                @if ($address->is_default)
                    <span class="address-card__badge">Mặc định</span>
                @endif

                <p class="address-card__name">{{ $address->recipient_name }} — {{ $address->phone }}</p>
                <p class="address-card__detail">
                    {{ $address->address_detail }}, {{ $address->ward }}, {{ $address->district }}, {{ $address->province }}
                </p>

                <div class="address-card__actions">
                    <a href="{{ route('addresses.edit', $address) }}">Sửa</a>

                    @if (! $address->is_default)
                        <form method="POST" action="{{ route('addresses.setDefault', $address) }}" style="display:inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="link-button">Đặt làm mặc định</button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('addresses.destroy', $address) }}" style="display:inline"
                          onsubmit="return confirm('Xóa địa chỉ này?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="link-button link-button--danger">Xóa</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>

    <p class="auth-card__footer">
        <a href="{{ route('profile.edit') }}">Quay lại thông tin cá nhân</a>
    </p>
</div>
@endsection
