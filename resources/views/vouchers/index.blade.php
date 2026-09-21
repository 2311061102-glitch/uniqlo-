@extends('layouts.app')

@section('title', 'Mã giảm giá')

@section('content')
<div class="voucher-page">
    <section class="voucher-hero">
        <div>
            <p class="voucher-hero__eyebrow">Ưu đãi dành cho bạn</p>
            <h1>Mã giảm giá đang có</h1>
            <p>Lưu lại mã yêu thích và nhập tại bước thanh toán để nhận ưu đãi tốt hơn.</p>
        </div>
        <div class="voucher-hero__mark" aria-hidden="true">%</div>
    </section>

    @if ($vouchers->isEmpty())
        <div class="voucher-empty">Hiện chưa có mã giảm giá nào khả dụng.</div>
    @else
        <div class="voucher-grid">
            @foreach ($vouchers as $voucher)
                <article class="voucher-card">
                    <div class="voucher-card__value">
                        @if ($voucher->type === 'percent')
                            Giảm {{ rtrim(rtrim(number_format($voucher->value, 2), '0'), '.') }}%
                            @if ($voucher->max_discount_amount)
                                <span class="voucher-card__cap">Tối đa {{ number_format($voucher->max_discount_amount, 0, ',', '.') }}₫</span>
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
                        <button type="button" class="voucher-copy-btn" data-code="{{ $voucher->code }}">Sao chép</button>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
document.querySelectorAll('.voucher-copy-btn').forEach((button) => {
    button.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(button.dataset.code);
            const original = button.textContent;
            button.textContent = 'Đã sao chép';
            setTimeout(() => { button.textContent = original; }, 1500);
        } catch (_) {
            button.textContent = 'Hãy chép mã';
        }
    });
});
</script>
@endpush
@endsection
