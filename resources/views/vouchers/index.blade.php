@extends('layouts.app')

@section('title', 'Mã giảm giá')

@section('content')
    <h1 class="page-title">🎟 Mã giảm giá đang có</h1>

    @if ($vouchers->isEmpty())
        <div class="cart-empty">
            <p>Hiện chưa có mã giảm giá nào khả dụng.</p>
        </div>
    @else
        <div class="voucher-grid">
            @foreach ($vouchers as $voucher)
                <div class="voucher-card">
                    <div class="voucher-card__value">
                        @if ($voucher->type === 'percent')
                            Giảm {{ rtrim(rtrim(number_format($voucher->value, 2), '0'), '.') }}%
                            @if ($voucher->max_discount_amount)
                                <span class="voucher-card__cap">(tối đa {{ number_format($voucher->max_discount_amount, 0, ',', '.') }}₫)</span>
                            @endif
                        @else
                            Giảm {{ number_format($voucher->value, 0, ',', '.') }}₫
                        @endif
                    </div>

                    <div class="voucher-card__condition">
                        @if ($voucher->min_order_amount > 0)
                            Đơn tối thiểu {{ number_format($voucher->min_order_amount, 0, ',', '.') }}₫
                        @else
                            Không yêu cầu đơn tối thiểu
                        @endif
                    </div>

                    @if ($voucher->end_date)
                        <div class="voucher-card__expiry">HSD: {{ $voucher->end_date->format('d/m/Y') }}</div>
                    @endif

                    <div class="voucher-card__code-row">
                        <span class="voucher-card__code">{{ $voucher->code }}</span>
                        <button type="button" class="btn-secondary voucher-copy-btn" data-code="{{ $voucher->code }}">Sao chép</button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @push('scripts')
        <script>
        document.querySelectorAll('.voucher-copy-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                navigator.clipboard.writeText(btn.dataset.code).then(() => {
                    const original = btn.textContent;
                    btn.textContent = 'Đã sao chép!';
                    setTimeout(() => { btn.textContent = original; }, 1500);
                });
            });
        });
        </script>
    @endpush
@endsection