@extends('layouts.app')

@section('title', 'Trang chủ (tạm thời)')

@section('content')
<div style="padding: 40px;">
    <h1>Trang chủ tạm thời</h1>
    <p>Trang chủ thật sẽ do Thành viên 2 xây dựng (banner, sản phẩm...).</p>

    @auth
        <p>✅ Đang đăng nhập với tài khoản: <strong>{{ auth()->user()->email }}</strong></p>
    @else
        <p>❌ Chưa đăng nhập.</p>
    @endauth
</div>
@endsection