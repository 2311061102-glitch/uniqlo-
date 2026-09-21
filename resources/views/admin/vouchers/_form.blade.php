{{--
    Dùng chung cho create.blade.php và edit.blade.php.
    Khi Sửa, biến $voucher đã tồn tại -> dùng ?? để tránh lỗi khi Tạo mới.
--}}

<div class="form-group">
    <label for="code">Mã voucher</label>
    <input type="text" id="code" name="code"
           value="{{ old('code', $voucher->code ?? '') }}"
           class="form-input @error('code') form-input--error @enderror"
           placeholder="VD: SALE50, FREESHIP" style="text-transform: uppercase;" required>
    @error('code') <p class="form-error">{{ $message }}</p> @enderror
</div>

<div class="form-group">
    <label for="type">Loại giảm giá</label>
    <select id="type" name="type" class="form-input @error('type') form-input--error @enderror" required>
        <option value="percent" {{ old('type', $voucher->type ?? '') == 'percent' ? 'selected' : '' }}>Giảm theo % đơn hàng</option>
        <option value="fixed" {{ old('type', $voucher->type ?? '') == 'fixed' ? 'selected' : '' }}>Giảm số tiền cố định</option>
    </select>
    @error('type') <p class="form-error">{{ $message }}</p> @enderror
</div>

<div class="form-group">
    <label for="value">Giá trị giảm (% nếu chọn "Giảm theo %", hoặc số tiền VNĐ nếu chọn "Giảm cố định")</label>
    <input type="number" step="0.01" id="value" name="value"
           value="{{ old('value', $voucher->value ?? '') }}"
           class="form-input @error('value') form-input--error @enderror" required>
    @error('value') <p class="form-error">{{ $message }}</p> @enderror
</div>

<div class="form-group">
    <label for="max_discount_amount">Giảm tối đa (VNĐ) — chỉ áp dụng cho loại "Giảm theo %", để trống nếu không giới hạn</label>
    <input type="number" step="0.01" id="max_discount_amount" name="max_discount_amount"
           value="{{ old('max_discount_amount', $voucher->max_discount_amount ?? '') }}"
           class="form-input @error('max_discount_amount') form-input--error @enderror">
    @error('max_discount_amount') <p class="form-error">{{ $message }}</p> @enderror
</div>

<div class="form-group">
    <label for="min_order_amount">Giá trị đơn hàng tối thiểu để áp dụng (VNĐ)</label>
    <input type="number" step="0.01" id="min_order_amount" name="min_order_amount"
           value="{{ old('min_order_amount', $voucher->min_order_amount ?? 0) }}"
           class="form-input @error('min_order_amount') form-input--error @enderror">
    @error('min_order_amount') <p class="form-error">{{ $message }}</p> @enderror
</div>

<div class="form-group">
    <label for="usage_limit">Giới hạn số lượt sử dụng (để trống = không giới hạn)</label>
    <input type="number" id="usage_limit" name="usage_limit"
           value="{{ old('usage_limit', $voucher->usage_limit ?? '') }}"
           class="form-input @error('usage_limit') form-input--error @enderror">
    @error('usage_limit') <p class="form-error">{{ $message }}</p> @enderror
    @if (isset($voucher))
        <p class="form-hint">Đã dùng: {{ $voucher->used_count }} lượt.</p>
    @endif
</div>

<div class="form-group">
    <label for="start_date">Ngày bắt đầu hiệu lực (để trống = có hiệu lực ngay)</label>
    <input type="date" id="start_date" name="start_date"
           value="{{ old('start_date', isset($voucher) && $voucher->start_date ? $voucher->start_date->format('Y-m-d') : '') }}"
           class="form-input @error('start_date') form-input--error @enderror">
    @error('start_date') <p class="form-error">{{ $message }}</p> @enderror
</div>

<div class="form-group">
    <label for="end_date">Ngày hết hạn (để trống = không giới hạn thời gian)</label>
    <input type="date" id="end_date" name="end_date"
           value="{{ old('end_date', isset($voucher) && $voucher->end_date ? $voucher->end_date->format('Y-m-d') : '') }}"
           class="form-input @error('end_date') form-input--error @enderror">
    @error('end_date') <p class="form-error">{{ $message }}</p> @enderror
</div>

<div class="form-group form-group--inline">
    <label>
        <input type="checkbox" name="is_active" value="1"
               @checked(old('is_active', $voucher->is_active ?? true))>
        Kích hoạt voucher này ngay
    </label>
</div>