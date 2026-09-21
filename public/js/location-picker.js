/**
 * Bộ chọn Tỉnh/Quận/Phường kiểu Shopee.
 * Dùng chung cho: trang "Thêm/Sửa địa chỉ" (addresses/_form.blade.php)
 * và popup "Thêm địa chỉ mới" trong Checkout.
 *
 * Cách dùng: mỗi field trên trang gọi initLocationPicker(rootEl) 1 lần,
 * rootEl là thẻ bọc ngoài chứa đủ: nút bấm mở picker, các input ẩn
 * (province/district/ward), và overlay picker (xem components/location-picker.blade.php).
 */

const VN_LOCATION_API = 'https://provinces.open-api.vn/api/v1';

// Cache lại dữ liệu đã tải để không gọi API lặp lại nhiều lần trong 1 phiên làm việc
const locationCache = {
    provinces: null,
    districtsByProvince: {},
    wardsByDistrict: {},
};

async function fetchProvinces() {
    if (locationCache.provinces) return locationCache.provinces;
    const res = await fetch(`${VN_LOCATION_API}/p/`);
    const data = await res.json();
    locationCache.provinces = data;
    return data;
}

async function fetchDistricts(provinceCode) {
    if (locationCache.districtsByProvince[provinceCode]) {
        return locationCache.districtsByProvince[provinceCode];
    }
    const res = await fetch(`${VN_LOCATION_API}/p/${provinceCode}?depth=2`);
    const data = await res.json();
    const districts = data.districts || [];
    locationCache.districtsByProvince[provinceCode] = districts;
    return districts;
}

async function fetchWards(districtCode) {
    if (locationCache.wardsByDistrict[districtCode]) {
        return locationCache.wardsByDistrict[districtCode];
    }
    const res = await fetch(`${VN_LOCATION_API}/d/${districtCode}?depth=2`);
    const data = await res.json();
    const wards = data.wards || [];
    locationCache.wardsByDistrict[districtCode] = wards;
    return wards;
}

function initLocationPicker(rootEl) {
    const openBtn = rootEl.querySelector('[data-lp-open]');
    const overlay = rootEl.querySelector('[data-lp-overlay]');
    const closeBtn = rootEl.querySelector('[data-lp-close]');
    const backBtn = rootEl.querySelector('[data-lp-back]');
    const searchInput = rootEl.querySelector('[data-lp-search]');
    const listEl = rootEl.querySelector('[data-lp-list]');
    const stepLabel = rootEl.querySelector('[data-lp-step-label]');
    const displayEl = rootEl.querySelector('[data-lp-display]');

    const provinceInput = rootEl.querySelector('[data-lp-input="province"]');
    const districtInput = rootEl.querySelector('[data-lp-input="district"]');
    const wardInput = rootEl.querySelector('[data-lp-input="ward"]');

    let step = 'province'; // province -> district -> ward
    let currentItems = [];
    let selectedProvince = null; // { code, name }
    let selectedDistrict = null; // { code, name }

    function renderList(items) {
        listEl.innerHTML = '';
        if (items.length === 0) {
            listEl.innerHTML = '<li class="lp-empty">Không tìm thấy kết quả.</li>';
            return;
        }
        items.forEach(item => {
            const li = document.createElement('li');
            li.className = 'lp-item';
            li.textContent = item.name;
            li.addEventListener('click', () => handleSelect(item));
            listEl.appendChild(li);
        });
    }

    function filterAndRender() {
        const keyword = (searchInput.value || '').toLowerCase().trim();
        const filtered = keyword
            ? currentItems.filter(i => i.name.toLowerCase().includes(keyword))
            : currentItems;
        renderList(filtered);
    }

    async function goToStep(newStep) {
        step = newStep;
        searchInput.value = '';
        listEl.innerHTML = '<li class="lp-loading">Đang tải...</li>';

        if (step === 'province') {
            stepLabel.textContent = 'Chọn Tỉnh / Thành phố';
            backBtn.style.display = 'none';
            currentItems = await fetchProvinces();
        } else if (step === 'district') {
            stepLabel.textContent = `Chọn Quận / Huyện — ${selectedProvince.name}`;
            backBtn.style.display = 'inline-block';
            currentItems = await fetchDistricts(selectedProvince.code);
        } else if (step === 'ward') {
            stepLabel.textContent = `Chọn Phường / Xã — ${selectedDistrict.name}`;
            backBtn.style.display = 'inline-block';
            currentItems = await fetchWards(selectedDistrict.code);
        }

        renderList(currentItems);
    }

    function handleSelect(item) {
        if (step === 'province') {
            selectedProvince = item;
            provinceInput.value = item.name;
            goToStep('district');
        } else if (step === 'district') {
            selectedDistrict = item;
            districtInput.value = item.name;
            goToStep('ward');
        } else if (step === 'ward') {
            wardInput.value = item.name;
            updateDisplay();
            closeOverlay();
        }
    }

    function updateDisplay() {
        const parts = [wardInput.value, districtInput.value, provinceInput.value].filter(Boolean);
        if (parts.length > 0) {
            displayEl.textContent = parts.join(', ');
            displayEl.classList.remove('lp-display--placeholder');
        }
    }

    function openOverlay() {
        overlay.style.display = 'flex';
        // Nếu đã có sẵn giá trị (VD: đang sửa địa chỉ), bắt đầu lại từ bước Tỉnh để chọn lại từ đầu
        goToStep('province');
    }

    function closeOverlay() {
        overlay.style.display = 'none';
    }

    openBtn.addEventListener('click', openOverlay);
    closeBtn.addEventListener('click', closeOverlay);
    backBtn.addEventListener('click', () => {
        if (step === 'ward') goToStep('district');
        else if (step === 'district') goToStep('province');
    });
    searchInput.addEventListener('input', filterAndRender);

    // Nếu form đã có sẵn dữ liệu (trang Sửa địa chỉ), hiển thị luôn không cần chọn lại
    updateDisplay();

    // Cho phép code bên ngoài (VD: JS của popup Checkout) tự set giá trị vào 3 input ẩn
    // rồi gọi hàm này để cập nhật lại dòng hiển thị, mà không cần mở lại popup chọn.
    rootEl._lpUpdateDisplay = updateDisplay;
    rootEl._lpReset = () => {
        provinceInput.value = '';
        districtInput.value = '';
        wardInput.value = '';
        displayEl.textContent = 'Chọn Tỉnh / Quận / Phường';
        displayEl.classList.add('lp-display--placeholder');
    };
}

// Tự động khởi tạo mọi bộ chọn địa chỉ có mặt trên trang khi DOM đã sẵn sàng
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-location-picker]').forEach(initLocationPicker);
});