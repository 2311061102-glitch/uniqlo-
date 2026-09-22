<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * Hiện danh sách địa chỉ của user đang đăng nhập (chỉ của họ, không thấy của người khác).
     * Địa chỉ mặc định luôn hiện lên đầu danh sách.
     */
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();

        return view('addresses.index', compact('addresses'));
    }

    public function create()
    {
        return view('addresses.create');
    }

    public function store(AddressRequest $request, AddressService $addressService)
    {
        $addressService->create($request->user(), $request->validated());

        return redirect()->route('addresses.index')->with('success', 'Thêm địa chỉ thành công!');
    }

    public function edit(Address $address)
    {
        $this->authorizeOwner($address);

        return view('addresses.edit', compact('address'));
    }

    public function update(AddressRequest $request, Address $address, AddressService $addressService)
    {
        $this->authorizeOwner($address);

        $addressService->update($address, $request->validated());

        return redirect()->route('addresses.index')->with('success', 'Cập nhật địa chỉ thành công!');
    }

    public function destroy(Address $address, AddressService $addressService)
    {
        $this->authorizeOwner($address);

        $addressService->delete($address);

        return back()->with('success', 'Đã xóa địa chỉ.');
    }

    public function setDefault(Address $address, AddressService $addressService)
    {
        $this->authorizeOwner($address);

        $addressService->setDefault($address);

        return back()->with('success', 'Đã đặt làm địa chỉ mặc định.');
    }

    /**
     * BẢO MẬT QUAN TRỌNG: chặn user A sửa/xóa địa chỉ của user B
     * dù họ có cố đoán đúng ID địa chỉ trên URL (ví dụ tự gõ /tai-khoan/dia-chi/5/sua).
     * Không được bỏ qua hàm này ở bất kỳ chỗ nào thao tác vào 1 địa chỉ cụ thể.
     */
    private function authorizeOwner(Address $address): void
    {
        abort_if($address->user_id !== auth()->id(), 403, 'Bạn không có quyền thao tác với địa chỉ này.');
    }
}
