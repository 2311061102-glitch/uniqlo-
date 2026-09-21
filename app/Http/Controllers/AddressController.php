<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Models\Address;
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

    public function store(AddressRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();

        $isFirstAddress = $user->addresses()->count() === 0;

        if ($isFirstAddress) {
            // Địa chỉ đầu tiên luôn tự động là mặc định, không cần user tự chọn
            $validated['is_default'] = true;
        } elseif (! empty($validated['is_default'])) {
            // Nếu user tick "đặt làm mặc định", bỏ mặc định của các địa chỉ CŨ trước
            // (tránh trường hợp có 2 địa chỉ cùng là mặc định)
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create($validated);

        return redirect()->route('addresses.index')->with('success', 'Thêm địa chỉ thành công!');
    }

    public function edit(Address $address)
    {
        $this->authorizeOwner($address);

        return view('addresses.edit', compact('address'));
    }

    public function update(AddressRequest $request, Address $address)
    {
        $this->authorizeOwner($address);

        $validated = $request->validated();

        if (! empty($validated['is_default'])) {
            $address->user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        } else {
            $validated['is_default'] = false;
        }

        $address->update($validated);

        return redirect()->route('addresses.index')->with('success', 'Cập nhật địa chỉ thành công!');
    }

    public function destroy(Address $address)
    {
        $this->authorizeOwner($address);

        $wasDefault = $address->is_default;
        $user = $address->user;

        $address->delete();

        // Nếu vừa xóa mất địa chỉ mặc định, tự động gán mặc định cho địa chỉ còn lại đầu tiên (nếu có)
        if ($wasDefault) {
            $user->addresses()->first()?->update(['is_default' => true]);
        }

        return back()->with('success', 'Đã xóa địa chỉ.');
    }

    public function setDefault(Address $address)
    {
        $this->authorizeOwner($address);

        $address->user->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

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
