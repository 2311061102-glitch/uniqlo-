{{--
    File này KHÔNG tự đứng riêng thành 1 trang, mà được @include vào
    create.blade.php và edit.blade.php để không phải viết trùng 2 lần.
    Khi ở trang "Thêm mới", biến $address chưa tồn tại -> dùng ?? để tránh lỗi.
--}}

<div class="form-group">
    <label for="recipient_name">Tên người nhận</label>
    <input type="text" id="recipient_name" name="recipient_name"
           value="{{ old('recipient_name', $address->recipient_name ?? '') }}"
           class="form-input @error('recipient_name') form-input--error @enderror" required>
    @error('recipient_name')
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>

<div class="form-group">
    <label for="phone">Số điện thoại</label>
    <input type="text" id="phone" name="phone"
           value="{{ old('phone', $address->phone ?? '') }}"
           class="form-input @error('phone') form-input--error @enderror" required>
    @error('phone')
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>

<div class="form-group">
    <label for="province">Tỉnh/Thành phố</label>
    <input type="text" id="province" name="province"
           value="{{ old('province', $address->province ?? '') }}"
           class="form-input @error('province') form-input--error @enderror" required>
    @error('province')
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>

<div class="form-group">
    <label for="district">Quận/Huyện</label>
    <input type="text" id="district" name="district"
           value="{{ old('district', $address->district ?? '') }}"
           class="form-input @error('district') form-input--error @enderror" required>
    @error('district')
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>

<div class="form-group">
    <label for="ward">Phường/Xã</label>
    <input type="text" id="ward" name="ward"
           value="{{ old('ward', $address->ward ?? '') }}"
           class="form-input @error('ward') form-input--error @enderror" required>
    @error('ward')
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>

<div class="form-group">
    <label for="address_detail">Địa chỉ cụ thể (số nhà, tên đường)</label>
    <input type="text" id="address_detail" name="address_detail"
           value="{{ old('address_detail', $address->address_detail ?? '') }}"
           class="form-input @error('address_detail') form-input--error @enderror" required>
    @error('address_detail')
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>

<div class="form-group form-group--inline">
    <label>
        <input type="checkbox" name="is_default" value="1"
               @checked(old('is_default', $address->is_default ?? false))>
        Đặt làm địa chỉ mặc định
    </label>
</div>
