{{--
    Component dùng chung: ô "Tỉnh/Quận/Phường" gộp thành 1 dòng, bấm vào mở
    popup chọn kiểu Shopee. Include file này ở bất kỳ đâu cần chọn địa chỉ.

    Cách dùng:
        @include('components.location-picker', [
            'idPrefix'   => 'addr',              // để nhiều picker trên cùng 1 trang không bị trùng id
            'province'   => $address->province ?? '',
            'district'   => $address->district ?? '',
            'ward'       => $address->ward ?? '',
        ])

    Sau khi người dùng chọn xong, giá trị được ghi vào 3 input ẩn:
    name="province", name="district", name="ward" — y hệt tên field cũ,
    nên Controller/Request phía sau KHÔNG cần sửa gì.
--}}

<div class="form-group" data-location-picker>
    <label>Tỉnh/Thành phố - Quận/Huyện - Phường/Xã</label>

    <button type="button" class="location-trigger" data-lp-open>
        <span data-lp-display class="lp-display {{ empty($province) ? 'lp-display--placeholder' : '' }}">
            {{ $province ? implode(', ', array_filter([$ward ?? '', $district ?? '', $province])) : 'Chọn Tỉnh / Quận / Phường' }}
        </span>
        <span class="location-trigger__arrow">›</span>
    </button>

    <input type="hidden" name="province" data-lp-input="province" value="{{ $province ?? '' }}" required>
    <input type="hidden" name="district" data-lp-input="district" value="{{ $district ?? '' }}" required>
    <input type="hidden" name="ward" data-lp-input="ward" value="{{ $ward ?? '' }}" required>

    @error('province') <p class="form-error">{{ $message }}</p> @enderror
    @error('district') <p class="form-error">{{ $message }}</p> @enderror
    @error('ward') <p class="form-error">{{ $message }}</p> @enderror

    {{-- Overlay/popup chọn địa chỉ --}}
    <div class="lp-overlay" data-lp-overlay style="display:none;">
        <div class="lp-panel">
            <div class="lp-panel__header">
                <button type="button" class="lp-back" data-lp-back style="display:none;">‹ Quay lại</button>
                <span data-lp-step-label class="lp-panel__title">Chọn Tỉnh / Thành phố</span>
                <button type="button" class="lp-close" data-lp-close>&times;</button>
            </div>

            <input type="text" class="lp-search" data-lp-search placeholder="Tìm kiếm...">

            <ul class="lp-list" data-lp-list></ul>
        </div>
    </div>
</div>