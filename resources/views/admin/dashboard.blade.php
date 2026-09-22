@extends('layouts.admin')

@section('title', 'Tổng quan')

@section('content')
    <div class="admin-topbar">
        <div><p class="admin-eyebrow">TRUNG TÂM ĐIỀU HÀNH</p><h1>Xin chào, {{ auth()->user()->name }}</h1><p class="admin-subtitle">Đây là tình hình vận hành cửa hàng hôm nay.</p></div>
        <div class="admin-topbar__actions"><span class="admin-date">{{ now()->translatedFormat('l, d/m/Y') }}</span><a class="admin-button admin-button--dark" href="{{ route('admin.vouchers.create') }}">+ Tạo khuyến mãi</a></div>
    </div>
    <section class="stats-grid" aria-label="Chỉ số tổng quan">
        @foreach ($stats as $stat)<article class="stat-card"><div class="stat-card__icon stat-card__icon--{{ $stat['tone'] }}">{{ $stat['icon'] }}</div><div><p>{{ $stat['label'] }}</p><strong>{{ $stat['value'] }}</strong><small>{{ $stat['trend'] }}</small></div></article>@endforeach
    </section>
    <section class="admin-overview-grid">
        <article class="admin-panel admin-panel--orders"><div class="admin-panel__header"><div><h2>Đơn hàng gần đây</h2><p>Cập nhật theo thời gian thực</p></div><a href="{{ route('admin.orders.index') }}">Xem tất cả →</a></div><div class="order-list">
            @forelse ($recentOrders as $order)
                @php $labels = ['pending' => 'Chờ xác nhận', 'processing' => 'Đang xử lý', 'shipping' => 'Đang giao', 'completed' => 'Hoàn thành', 'cancelled' => 'Đã hủy']; $status = $order->order_status; @endphp
                <div class="admin-order-row"><div class="order-avatar">{{ mb_strtoupper(mb_substr($order->recipient_name, 0, 1)) }}</div><div class="admin-order-row__customer"><strong>{{ $order->recipient_name }}</strong><span>#{{ $order->order_code }} · {{ $order->created_at->format('d/m, H:i') }}</span></div><div class="admin-order-row__amount">{{ number_format($order->total_amount, 0, ',', '.') }}₫</div><span class="admin-status admin-status--{{ $status }}">{{ $labels[$status] ?? $status }}</span></div>
            @empty <p class="admin-empty">Chưa có đơn hàng nào.</p> @endforelse
        </div></article>
        <article class="admin-panel admin-panel--status"><div class="admin-panel__header"><div><h2>Tiến độ đơn hàng</h2><p>Phân bổ toàn bộ đơn hàng</p></div></div><div class="status-ring"><strong>{{ $orderStatusCounts->sum() }}</strong><span>tổng đơn</span></div><div class="status-breakdown">
            @foreach (['pending' => ['Chờ xác nhận', 'yellow'], 'processing' => ['Đang xử lý', 'blue'], 'shipping' => ['Đang giao', 'purple'], 'completed' => ['Hoàn thành', 'green']] as $key => [$label, $tone])<div><span><i class="status-dot status-dot--{{ $tone }}"></i>{{ $label }}</span><strong>{{ $orderStatusCounts[$key] ?? 0 }}</strong></div>@endforeach
        </div></article>
    </section>
    <section class="admin-panel inventory-panel"><div class="admin-panel__header"><div><h2>Cảnh báo tồn kho</h2><p>Sản phẩm còn từ 10 sản phẩm trở xuống</p></div><a href="{{ route('admin.products.index') }}">Quản lý sản phẩm →</a></div><div class="inventory-list">
        @forelse ($lowStockProducts as $product)<div class="inventory-row"><div class="inventory-thumb">{{ mb_strtoupper(mb_substr($product->name, 0, 1)) }}</div><div><strong>{{ $product->name }}</strong><span>{{ $product->category?->name ?? 'Chưa phân loại' }}</span></div><div class="inventory-row__stock"><strong>{{ $product->variants->sum('stock_quantity') }}</strong><span>sản phẩm còn lại</span></div><a href="{{ route('products.show', $product) }}">Xem</a></div>@empty <p class="admin-empty">Kho hàng đang ở mức an toàn.</p> @endforelse
    </div></section>
@endsection
