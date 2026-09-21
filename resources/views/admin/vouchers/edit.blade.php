@extends('layouts.admin')

@section('title', 'Sửa voucher')

@section('content')
    <h1 class="page-title">Sửa voucher {{ $voucher->code }}</h1>

    <form action="{{ route('admin.vouchers.update', $voucher) }}" method="POST" class="admin-form">
        @csrf
        @method('PUT')
        @include('admin.vouchers._form')
        <button type="submit" class="btn-primary">Cập nhật</button>
    </form>

    <a href="{{ route('admin.vouchers.index') }}" class="link-button">&larr; Quay lại danh sách</a>
@endsection