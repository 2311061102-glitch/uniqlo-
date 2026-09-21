@extends('layouts.admin')

@section('title', 'Quản lý Voucher')

@section('content')
    <div class="admin-page-header">
        <h1 class="page-title">Quản lý Voucher</h1>
        <a href="{{ route('admin.vouchers.create') }}" class="btn-primary">+ Tạo voucher mới</a>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th>Mã</th>
                <th>Loại</th>
                <th>Giá trị</th>
                <th>Đơn tối thiểu</th>
                <th>Đã dùng / Giới hạn</th>
                <th>Hiệu lực</th>
                <th>Trạng thái</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($vouchers as $voucher)
                <tr>
                    <td><strong>{{ $voucher->code }}</strong></td>
                    <td>{{ $voucher->type === 'percent' ? 'Giảm %' : 'Giảm tiền' }}</td>
                    <td>
                        {{ $voucher->type === 'percent' ? $voucher->value . '%' : number_format($voucher->value, 0, ',', '.') . '₫' }}
                        @if ($voucher->type === 'percent' && $voucher->max_discount_amount)
                            <br><small>(tối đa {{ number_format($voucher->max_discount_amount, 0, ',', '.') }}₫)</small>
                        @endif
                    </td>
                    <td>{{ number_format($voucher->min_order_amount, 0, ',', '.') }}₫</td>
                    <td>{{ $voucher->used_count }} / {{ $voucher->usage_limit ?? '∞' }}</td>
                    <td>
                        {{ $voucher->start_date?->format('d/m/Y') ?? '—' }}
                        →
                        {{ $voucher->end_date?->format('d/m/Y') ?? '—' }}
                    </td>
                    <td>
                        <form action="{{ route('admin.vouchers.toggle-active', $voucher) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="status-badge status-badge--{{ $voucher->is_active ? 'completed' : 'cancelled' }}" style="border:none; cursor:pointer;">
                                {{ $voucher->is_active ? 'Đang bật' : 'Đã tắt' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <a href="{{ route('admin.vouchers.edit', $voucher) }}" class="link-button">Sửa</a>
                        <form action="{{ route('admin.vouchers.destroy', $voucher) }}" method="POST" onsubmit="return confirm('Xóa voucher {{ $voucher->code }}?');" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-remove">Xóa</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:20px;">Chưa có voucher nào.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pagination-wrapper">
        {{ $vouchers->links() }}
    </div>
@endsection