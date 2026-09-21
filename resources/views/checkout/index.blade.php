@extends('layouts.app')

@section('title', 'Thanh toán')

@section('content')
    <h1 class="page-title">Thông tin thanh toán</h1>

    @if (session('error'))
        <div class="alert alert--error">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert--error">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="checkout-layout">
        <form action="{{ route('checkout.store') }}" method="POST" class="checkout-form">
            @csrf

            @php $selectedAddress = $addresses->firstWhere('id', $selectedAddressId); @endphp

            <div class="form-group">
                <label>Địa chỉ nhận hàng</label>

                <button type="button" class="address-summary" id="open-address-modal-btn">
                    <span class="address-summary__text">
                        @if ($selectedAddress)
                            <strong>{{ $selectedAddress->recipient_name }}</strong> - {{ $selectedAddress->phone }}<br>
                            {{ $selectedAddress->address_detail }}, {{ $selectedAddress->ward }}, {{ $selectedAddress->district }}, {{ $selectedAddress->province }}
                        @else
                            Chọn địa chỉ nhận hàng
                        @endif
                    </span>
                    <span class="address-summary__arrow">›</span>
                </button>
            </div>

            {{-- Giá trị thật gửi kèm đơn hàng, luôn đồng bộ với lựa chọn ở trên --}}
            <input type="hidden" name="address_id" value="{{ $selectedAddressId }}">

            <div class="form-group">
                <label for="shipping_region">Khu vực giao hàng</label>
                <select name="shipping_region" id="shipping_region" form="region-form" onchange="this.form.submit()">
                    @foreach ($regionOptions as $value => $label)
                        <option value="{{ $value }}" {{ $selectedRegion == $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="form-hint">Đơn từ 500.000₫ được miễn phí vận chuyển.</p>
                {{-- Giá trị thật gửi kèm đơn hàng, luôn đồng bộ với lựa chọn ở trên --}}
                <input type="hidden" name="shipping_region" value="{{ $selectedRegion }}">
            </div>

            <div class="form-group">
    <label>Phương thức thanh toán</label>
    <div class="payment-method-list">
        <label class="payment-method-option">
            <input type="radio" name="payment_method" value="cod" checked>
            <span class="payment-method-option__label">Thanh toán khi nhận hàng</span>
            <span class="payment-method-option__icon">💵</span>
        </label>
        <label class="payment-method-option">
            <input type="radio" name="payment_method" value="bank_transfer">
            <span class="payment-method-option__label">Chuyển khoản ngân hàng</span>
            <span class="payment-method-option__icon">🏦</span>
        </label>
        <label class="payment-method-option">
            <input type="radio" name="payment_method" value="qr">
            <span class="payment-method-option__label">Thanh toán bằng QR</span>
            <span class="payment-method-option__icon">📱</span>
        </label>
    </div>
    </div>

            <button type="submit" class="btn-primary">Đặt hàng</button>
        </form>

        {{-- Form ẩn riêng để đổi khu vực giao hàng, chỉ tính lại phí ship, chưa đặt hàng thật --}}
        <form id="region-form" action="{{ route('checkout.region') }}" method="POST" style="display:none;">
            @csrf
        </form>

        {{-- Form ẩn riêng để đổi địa chỉ đang chọn, lưu tạm vào session --}}
        <form id="address-form" action="{{ route('checkout.address') }}" method="POST" style="display:none;">
            @csrf
        </form>

        <div class="checkout-summary">
            <h2>Đơn hàng của bạn</h2>

            @foreach ($selectedItems as $item)
                <div class="checkout-summary__item">
                    <span>{{ $item->variant->product->name }} ({{ $item->variant->size }} - {{ $item->variant->color }}) x{{ $item->quantity }}</span>
                    <span>{{ number_format($item->variant->final_price * $item->quantity, 0, ',', '.') }}₫</span>
                </div>
            @endforeach

            <hr>

            {{-- Mã giảm giá --}}
            <div class="voucher-box">
                @if ($voucher)
                    <div class="voucher-applied">
                        <span>🎟 Đã áp dụng mã <strong>{{ $voucher->code }}</strong></span>
                        <form action="{{ route('checkout.voucher.remove') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="link-button">Gỡ</button>
                        </form>
                    </div>
                @else
                    <form action="{{ route('checkout.voucher.apply') }}" method="POST" class="voucher-form">
                        @csrf
                        <input type="text" name="voucher_code" placeholder="Nhập mã giảm giá">
                        <button type="submit" class="btn-secondary">Áp dụng</button>
                    </form>
                @endif
            </div>

            <hr>

            <div class="checkout-summary__line">
                <span>Tạm tính</span>
                <span>{{ number_format($subtotal, 0, ',', '.') }}₫</span>
            </div>
            <div class="checkout-summary__line">
                <span>Phí vận chuyển</span>
                <span>{{ $shippingFee > 0 ? number_format($shippingFee, 0, ',', '.') . '₫' : 'Miễn phí' }}</span>
            </div>
            @if ($discount > 0)
                <div class="checkout-summary__line checkout-summary__line--discount">
                    <span>Giảm giá</span>
                    <span>-{{ number_format($discount, 0, ',', '.') }}₫</span>
                </div>
            @endif

            <p class="checkout-summary__total">Tổng tiền: <span>{{ number_format($total, 0, ',', '.') }}₫</span></p>
        </div>
        </div>

    {{-- ===== Popup chọn / thêm địa chỉ (kiểu Shopee) ===== --}}
    <div class="lp-overlay" id="address-modal-overlay" style="display:none;">
        <div class="lp-panel address-modal-panel">
            <div class="lp-panel__header">
                <span class="lp-panel__title">Địa chỉ nhận hàng</span>
                <button type="button" class="lp-close" id="close-address-modal-btn">&times;</button>
            </div>

            {{-- Tab 1: chọn 1 địa chỉ đã lưu --}}
            <div id="address-modal-list-view">
                <div class="address-modal-list">
                    @foreach ($addresses as $address)
                        <div class="address-modal-item">
                            <label class="address-option">
                                <input type="radio" name="address_id" value="{{ $address->id }}"
                                       form="address-form"
                                       {{ $selectedAddressId == $address->id ? 'checked' : '' }}>
                                <span>
                                    <strong>{{ $address->recipient_name }}</strong> - {{ $address->phone }}<br>
                                    {{ $address->address_detail }}, {{ $address->ward }}, {{ $address->district }}, {{ $address->province }}
                                    @if ($address->is_default) <em>(Mặc định)</em> @endif
                                </span>
                            </label>
                            <button type="button" class="link-button address-edit-btn" data-address-id="{{ $address->id }}">Sửa</button>
                        </div>
                    @endforeach
                </div>

                <button type="button" class="link-button" id="show-add-address-form-btn">+ Thêm địa chỉ mới</button>

                <div class="address-modal-actions">
                    <button type="button" class="btn-primary" id="confirm-address-btn">Xác nhận</button>
                </div>
            </div>

            {{-- Tab 2: form thêm MỚI / SỬA địa chỉ (dùng chung 1 form, gửi AJAX, không rời trang Checkout) --}}
            <div id="address-modal-create-view" style="display:none;">
                <p id="quick-form-title" class="lp-panel__title" style="margin-bottom: var(--spacing12, 12px);">Thêm địa chỉ mới</p>

                <div id="quick-address-errors"></div>

                <div class="form-group">
                    <label for="quick_recipient_name">Tên người nhận</label>
                    <input type="text" id="quick_recipient_name" class="form-input">
                </div>

                <div class="form-group">
                    <label for="quick_phone">Số điện thoại</label>
                    <input type="text" id="quick_phone" class="form-input">
                </div>

                @include('components.location-picker', [
                    'idPrefix' => 'quick-address',
                    'province' => '',
                    'district' => '',
                    'ward'     => '',
                ])

                <div class="form-group">
                    <label for="quick_address_detail">Địa chỉ cụ thể (số nhà, tên đường)</label>
                    <input type="text" id="quick_address_detail" class="form-input">
                </div>

                <div class="form-group form-group--inline">
                    <label>
                        <input type="checkbox" id="quick_is_default">
                        Đặt làm địa chỉ mặc định
                    </label>
                </div>

                <div class="address-modal-actions">
                    <button type="button" class="link-button" id="back-to-address-list-btn">‹ Quay lại</button>
                    <button type="button" class="btn-primary" id="save-quick-address-btn">Lưu địa chỉ</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Form ẩn để "Xác nhận" trong modal gửi lựa chọn địa chỉ về server (giống hidden form khu vực) --}}
    <form id="address-form" action="{{ route('checkout.address') }}" method="POST" style="display:none;">
        @csrf
    </form>

    @push('scripts')
        <script src="{{ asset('js/location-picker.js') }}"></script>
        <script>
        (function () {
            // Dữ liệu đầy đủ của các địa chỉ đã lưu, để điền lại vào form khi bấm "Sửa"
            const addressesData = @json($addresses->keyBy('id'));

            const modalOverlay = document.getElementById('address-modal-overlay');
            const listView = document.getElementById('address-modal-list-view');
            const createView = document.getElementById('address-modal-create-view');
            const formTitle = document.getElementById('quick-form-title');

            const recipientNameInput = document.getElementById('quick_recipient_name');
            const phoneInput = document.getElementById('quick_phone');
            const addressDetailInput = document.getElementById('quick_address_detail');
            const isDefaultInput = document.getElementById('quick_is_default');

            const provinceHidden = document.querySelector('[data-location-picker] [data-lp-input="province"]');
            const districtHidden = document.querySelector('[data-location-picker] [data-lp-input="district"]');
            const wardHidden = document.querySelector('[data-location-picker] [data-lp-input="ward"]');
            const locationDisplay = document.querySelector('[data-location-picker] [data-lp-display]');

            // null = đang ở chế độ Thêm mới. Có giá trị = đang Sửa địa chỉ có ID này.
            let editingAddressId = null;

            function openModal() {
                modalOverlay.style.display = 'flex';
                showListView();
            }

            function showListView() {
                listView.style.display = 'block';
                createView.style.display = 'none';
            }

            function resetQuickForm() {
                editingAddressId = null;
                formTitle.textContent = 'Thêm địa chỉ mới';
                recipientNameInput.value = '';
                phoneInput.value = '';
                addressDetailInput.value = '';
                isDefaultInput.checked = false;
                provinceHidden.value = '';
                districtHidden.value = '';
                wardHidden.value = '';
                locationDisplay.textContent = 'Chọn Tỉnh / Quận / Phường';
                locationDisplay.classList.add('lp-display--placeholder');
                document.getElementById('quick-address-errors').innerHTML = '';
            }

            function fillQuickFormForEdit(address) {
                editingAddressId = address.id;
                formTitle.textContent = 'Sửa địa chỉ';
                recipientNameInput.value = address.recipient_name;
                phoneInput.value = address.phone;
                addressDetailInput.value = address.address_detail;
                isDefaultInput.checked = !!address.is_default;
                provinceHidden.value = address.province;
                districtHidden.value = address.district;
                wardHidden.value = address.ward;
                locationDisplay.textContent = [address.ward, address.district, address.province].join(', ');
                locationDisplay.classList.remove('lp-display--placeholder');
                document.getElementById('quick-address-errors').innerHTML = '';
            }

            document.getElementById('open-address-modal-btn').addEventListener('click', openModal);

            document.getElementById('close-address-modal-btn').addEventListener('click', () => {
                modalOverlay.style.display = 'none';
            });

            document.getElementById('confirm-address-btn').addEventListener('click', () => {
                const checked = document.querySelector('input[name="address_id"]:checked');
                if (!checked) {
                    alert('Vui lòng chọn 1 địa chỉ.');
                    return;
                }
                // Gửi lựa chọn lên server (lưu vào session), rồi tải lại trang để cập nhật đúng dữ liệu
                document.getElementById('address-form').submit();
            });

            document.getElementById('show-add-address-form-btn').addEventListener('click', () => {
                resetQuickForm();
                listView.style.display = 'none';
                createView.style.display = 'block';
            });

            // Bấm "Sửa" trên 1 địa chỉ trong danh sách -> mở form, điền sẵn dữ liệu địa chỉ đó
            document.querySelectorAll('.address-edit-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const address = addressesData[btn.dataset.addressId];
                    if (!address) return;

                    fillQuickFormForEdit(address);
                    listView.style.display = 'none';
                    createView.style.display = 'block';
                });
            });

            document.getElementById('back-to-address-list-btn').addEventListener('click', showListView);

            document.getElementById('save-quick-address-btn').addEventListener('click', () => {
                const errorsBox = document.getElementById('quick-address-errors');
                errorsBox.innerHTML = '';

                const payload = {
                    recipient_name: recipientNameInput.value,
                    phone: phoneInput.value,
                    province: provinceHidden.value,
                    district: districtHidden.value,
                    ward: wardHidden.value,
                    address_detail: addressDetailInput.value,
                    is_default: isDefaultInput.checked ? 1 : 0,
                };

                // Đang Sửa -> gọi PUT tới đúng địa chỉ đó. Đang Thêm mới -> gọi POST như cũ.
                const url = editingAddressId
                    ? `{{ url('/tai-khoan/dia-chi') }}/${editingAddressId}`
                    : '{{ route("addresses.store") }}';
                const method = editingAddressId ? 'PUT' : 'POST';

                fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(payload),
                })
                    .then(res => res.json().then(data => ({ status: res.status, data })))
                    .then(({ status, data }) => {
                        if (status === 422) {
                            const messages = Object.values(data.errors || {}).flat();
                            errorsBox.innerHTML = messages.map(m => `<p class="form-error">${m}</p>`).join('');
                            return;
                        }
                        if (status !== 200 || !data.success) {
                            alert(data.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                            return;
                        }

                        // Chọn luôn địa chỉ vừa lưu (mới hoặc vừa sửa) rồi tải lại trang Checkout
                        fetch('{{ route("checkout.address") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({ address_id: data.address.id }),
                        }).finally(() => window.location.reload());
                    })
                    .catch(() => {
                        alert('Có lỗi khi kết nối tới server, vui lòng thử lại.');
                    });
            });
        })();
        </script>
    @endpush
@endsection