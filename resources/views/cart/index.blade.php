@extends('layouts.app')

@section('title', 'Giỏ hàng')

@section('content')
    <h1 class="page-title">Giỏ hàng</h1>

    @if (session('error'))
        <div class="alert alert--error">{{ session('error') }}</div>
    @endif

    @if ($cart->items->isEmpty())
        <div class="cart-empty">
            <p>Giỏ hàng đang trống.</p>
            <a href="{{ route('products.index') }}" class="btn-primary btn-primary--inline">Tiếp tục mua hàng</a>
        </div>
    @else
        <table class="cart-table">
            <thead>
                <tr>
                    <th style="width:40px;">
                        <input type="checkbox" id="select-all-checkbox">
                    </th>
                    <th>Sản phẩm</th>
                    <th>Đơn giá</th>
                    <th>Số lượng</th>
                    <th>Thành tiền</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cart->items as $item)
                    <tr>
                        <td>
                            <input
                                type="checkbox"
                                name="selected_items[]"
                                value="{{ $item->id }}"
                                class="item-checkbox"
                                data-price="{{ $item->variant->final_price }}"
                                data-qty="{{ $item->quantity }}"
                                form="cart-checkout-form">
                        </td>
                        <td class="cart-table__product">
                            <img
                                src="{{ $item->variant->images->first() ? asset('storage/' . $item->variant->images->first()->image_path) : ($item->variant->product->primary_image ? asset('storage/' . $item->variant->product->primary_image->image_path) : '') }}"
                                alt="{{ $item->variant->product->name }}">
                            <div>
                                <span class="cart-table__name">{{ $item->variant->product->name }}</span>
                                <span class="cart-table__variant">{{ $item->variant->size }} - {{ $item->variant->color }}</span>
                            </div>
                        </td>
                        <td>{{ number_format($item->variant->final_price, 0, ',', '.') }}₫</td>
                        <td>
                            {{-- Form này ĐỘC LẬP, không còn bị lồng trong form nào khác --}}
                            <form action="{{ route('cart.update', $item) }}" method="POST" class="qty-form">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="quantity" value="{{ $item->quantity }}" min="1" max="{{ $item->variant->stock_quantity }}">
                                <button type="submit" class="btn-secondary">Cập nhật</button>
                            </form>
                        </td>
                        <td>{{ number_format($item->variant->final_price * $item->quantity, 0, ',', '.') }}₫</td>
                        <td>
                            <form action="{{ route('cart.remove', $item) }}" method="POST" onsubmit="return confirm('Xóa sản phẩm này khỏi giỏ hàng?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-remove">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="cart-summary">
            <p class="cart-summary__total">
                Đã chọn <span id="selected-count">0</span> sản phẩm — Tổng tiền:
                <span id="selected-total">0₫</span>
            </p>
            <p class="form-hint">* Chưa gồm phí vận chuyển và mã giảm giá — sẽ tính ở bước thanh toán.</p>

            <div class="cart-summary__actions">
                <a href="{{ route('products.index') }}" class="btn-secondary">Tiếp tục mua hàng</a>

                <button type="submit" form="cart-clear-form" class="btn-secondary" onclick="return confirm('Xóa toàn bộ giỏ hàng?');">Xóa toàn bộ giỏ hàng</button>

                @auth
                    <button type="submit" form="cart-checkout-form" id="checkout-btn" class="btn-primary btn-primary--inline" disabled>Tiến hành thanh toán</button>
                @else
                    <a href="{{ route('login') }}" class="btn-primary btn-primary--inline">Đăng nhập để thanh toán</a>
                @endauth
            </div>
        </div>

        {{-- 2 form ẩn, ĐỘC LẬP hoàn toàn ngoài bảng — không lồng vào form/element nào khác.
             Các input/button ở trên "trỏ" vào đây qua thuộc tính form="..." --}}
        <form action="{{ route('cart.select-for-checkout') }}" method="POST" id="cart-checkout-form" style="display:none;">
            @csrf
        </form>

        <form action="{{ route('cart.clear') }}" method="POST" id="cart-clear-form" style="display:none;">
            @csrf
            @method('DELETE')
        </form>
    @endif

    @push('scripts')
    <script>
    (function () {
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        const itemCheckboxes = document.querySelectorAll('.item-checkbox');
        const selectedCountEl = document.getElementById('selected-count');
        const selectedTotalEl = document.getElementById('selected-total');
        const checkoutBtn = document.getElementById('checkout-btn');

        function formatVnd(number) {
            return new Intl.NumberFormat('vi-VN').format(number) + '₫';
        }

        function recalculate() {
            let count = 0;
            let total = 0;

            itemCheckboxes.forEach(cb => {
                if (cb.checked) {
                    count += 1;
                    total += parseFloat(cb.dataset.price) * parseInt(cb.dataset.qty, 10);
                }
            });

            selectedCountEl.textContent = count;
            selectedTotalEl.textContent = formatVnd(total);

            if (checkoutBtn) {
                checkoutBtn.disabled = count === 0;
            }

            if (selectAllCheckbox) {
                selectAllCheckbox.checked = count === itemCheckboxes.length && count > 0;
            }
        }

        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', () => {
                itemCheckboxes.forEach(cb => { cb.checked = selectAllCheckbox.checked; });
                recalculate();
            });
        }

        itemCheckboxes.forEach(cb => cb.addEventListener('change', recalculate));

        recalculate();
    })();
    </script>
    @endpush
@endsection