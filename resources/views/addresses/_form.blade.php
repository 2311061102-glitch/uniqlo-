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

<div class="address-map-picker">
    <div class="address-map-picker__heading">
        <div>
            <label>Ghim vị trí trên bản đồ <span class="address-map-picker__optional">(khuyên dùng)</span></label>
            <p class="address-map-picker__hint">Chọn đúng vị trí để hệ thống tính phí giao hàng theo khoảng cách.</p>
        </div>
        <button type="button" class="btn-secondary address-map-picker__locate" data-address-locate>Lấy vị trí hiện tại</button>
    </div>
    <div id="address-map" class="address-map-picker__map"></div>
    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $address->latitude ?? '') }}">
    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $address->longitude ?? '') }}">
    <p class="address-map-picker__coordinates" data-address-coordinates></p>
    @error('latitude') <p class="form-error">{{ $message }}</p> @enderror
    @error('longitude') <p class="form-error">{{ $message }}</p> @enderror
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

@once
    @push('scripts')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const mapElement = document.getElementById('address-map');
                if (!mapElement || typeof L === 'undefined') return;
                const latitudeInput = document.getElementById('latitude');
                const longitudeInput = document.getElementById('longitude');
                const coordinates = document.querySelector('[data-address-coordinates]');
                const defaultCenter = [16.0471, 108.2068];
                const initial = latitudeInput.value && longitudeInput.value
                    ? [Number(latitudeInput.value), Number(longitudeInput.value)]
                    : defaultCenter;
                const map = L.map(mapElement).setView(initial, latitudeInput.value ? 15 : 6);
                const tileOptions = {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors'
                };
                let tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', tileOptions).addTo(map);
                let fallbackLoaded = false;
                tileLayer.on('tileerror', function () {
                    if (fallbackLoaded) return;
                    fallbackLoaded = true;
                    map.removeLayer(tileLayer);
                    tileLayer = L.tileLayer('https://{s}.tile.openstreetmap.de/{z}/{x}/{y}.png', tileOptions).addTo(map);
                });
                let marker = null;

                async function reverseGeocode(latitude, longitude) {
                    coordinates.textContent = 'Đang tìm địa chỉ từ vị trí đã chọn…';
                    try {
                        let data;
                        const response = await fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + encodeURIComponent(latitude) + '&lon=' + encodeURIComponent(longitude) + '&accept-language=vi');
                        if (response.ok) data = await response.json();
                        if (!data) {
                            const fallback = await fetch('https://api.bigdatacloud.net/data/reverse-geocode-client?latitude=' + encodeURIComponent(latitude) + '&longitude=' + encodeURIComponent(longitude) + '&localityLanguage=vi');
                            if (!fallback.ok) throw new Error('reverse geocoding failed');
                            const result = await fallback.json();
                            const administrative = result.localityInfo?.administrative || [];
                            data = {
                                address: {
                                    state: result.principalSubdivision,
                                    city: result.city,
                                    district: administrative.find(item => /district|quận|huyện/i.test(item.name || ''))?.name,
                                    suburb: result.locality,
                                },
                                display_name: result.locality || ''
                            };
                        }
                        const address = data.address || {};
                        const displayName = data.display_name || [latitudeInput.value, longitudeInput.value].join(', ');
                        const setValue = (id, value) => {
                            const input = document.getElementById(id);
                            if (input && value) input.value = value;
                        };
                        const province = address.state || address.province || address.city || displayName;
                        const district = address.city_district || address.district || address.county || address.town || province;
                        const ward = address.suburb || address.quarter || address.village || address.town || district;
                        setValue('province', province);
                        setValue('district', district);
                        setValue('ward', ward);
                        const detail = [address.house_number, address.road].filter(Boolean).join(' ');
                        setValue('address_detail', detail || displayName);
                        coordinates.textContent = 'Đã ghim và điền địa chỉ tự động: ' + latitudeInput.value + ', ' + longitudeInput.value;
                    } catch (error) {
                        const gpsText = 'Vị trí GPS ' + latitudeInput.value + ', ' + longitudeInput.value;
                        document.getElementById('province').value = document.getElementById('province').value || gpsText;
                        document.getElementById('district').value = document.getElementById('district').value || gpsText;
                        document.getElementById('ward').value = document.getElementById('ward').value || gpsText;
                        document.getElementById('address_detail').value = document.getElementById('address_detail').value || gpsText;
                        coordinates.textContent = 'Đã điền vị trí GPS. Bạn có thể sửa lại tên địa chỉ cho dễ nhận hàng.';
                    }
                }

                function setLocation(latitude, longitude, lookup = true) {
                    latitudeInput.value = Number(latitude).toFixed(7);
                    longitudeInput.value = Number(longitude).toFixed(7);
                    if (!marker) marker = L.marker([latitude, longitude], {draggable: true}).addTo(map);
                    else marker.setLatLng([latitude, longitude]);
                    marker.off('dragend').on('dragend', function () {
                        const position = marker.getLatLng();
                        setLocation(position.lat, position.lng);
                    });
                    map.setView([latitude, longitude], Math.max(map.getZoom(), 15));
                    coordinates.textContent = 'Đã ghim: ' + latitudeInput.value + ', ' + longitudeInput.value;
                    if (lookup) reverseGeocode(latitude, longitude);
                }
                if (latitudeInput.value && longitudeInput.value) setLocation(initial[0], initial[1], false);
                map.on('click', event => setLocation(event.latlng.lat, event.latlng.lng));
                document.querySelector('[data-address-locate]')?.addEventListener('click', function () {
                    if (!navigator.geolocation) { alert('Trình duyệt không hỗ trợ định vị.'); return; }
                    this.disabled = true;
                    const button = this;
                    navigator.geolocation.getCurrentPosition(
                        position => setLocation(position.coords.latitude, position.coords.longitude),
                        error => alert(error.code === 1
                            ? 'Bạn chưa cấp quyền vị trí cho trình duyệt.'
                            : 'Không thể lấy vị trí hiện tại. Hãy chọn trực tiếp trên bản đồ.'),
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                    setTimeout(() => { button.disabled = false; }, 1000);
                });
            });
        </script>
    @endpush
@endonce

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
