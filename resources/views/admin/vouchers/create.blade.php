@extends('layouts.admin')

@section('title', 'Tạo voucher mới')

@section('content')
    <h1 class="page-title">Tạo voucher mới</h1>

    <form action="{{ route('admin.vouchers.store') }}" method="POST" class="admin-form">
        @csrf
        @include('admin.vouchers._form')
        <button type="submit" class="btn-primary">Lưu voucher</button>
    </form>

    <a href="{{ route('admin.vouchers.index') }}" class="link-button">&larr; Quay lại danh sách</a>
@endsection