<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    /**
     * Danh sách voucher.
     */
    public function index()
    {
        $this->assertAdmin();

        $vouchers = Voucher::latest()->paginate(15);

        return view('admin.vouchers.index', compact('vouchers'));
    }

    /**
     * Form tạo voucher mới.
     */
    public function create()
    {
        $this->assertAdmin();

        return view('admin.vouchers.create');
    }

    /**
     * Lưu voucher mới.
     */
    public function store(Request $request)
    {
        $this->assertAdmin();

        $validated = $this->validateVoucher($request);

        Voucher::create($validated);

        return redirect()->route('admin.vouchers.index')->with('success', 'Đã tạo voucher thành công.');
    }

    /**
     * Form sửa voucher.
     */
    public function edit(Voucher $voucher)
    {
        $this->assertAdmin();

        return view('admin.vouchers.edit', compact('voucher'));
    }

    /**
     * Cập nhật voucher.
     */
    public function update(Request $request, Voucher $voucher)
    {
        $this->assertAdmin();

        $validated = $this->validateVoucher($request, $voucher);

        $voucher->update($validated);

        return redirect()->route('admin.vouchers.index')->with('success', 'Đã cập nhật voucher.');
    }

    /**
     * Xóa voucher.
     */
    public function destroy(Voucher $voucher)
    {
        $this->assertAdmin();

        $voucher->delete();

        return redirect()->route('admin.vouchers.index')->with('success', 'Đã xóa voucher.');
    }

    /**
     * Bật/tắt nhanh trạng thái hoạt động của voucher (không cần vào form sửa).
     */
    public function toggleActive(Voucher $voucher)
    {
        $this->assertAdmin();

        $voucher->update(['is_active' => ! $voucher->is_active]);

        return back()->with('success', $voucher->is_active ? 'Đã bật voucher.' : 'Đã tắt voucher.');
    }

    /**
     * BẢO MẬT: chặn mọi tài khoản không phải admin truy cập khu vực này,
     * gọi ở đầu MỌI method trong Controller này (giống cách AddressController
     * chặn user sửa địa chỉ của người khác bằng authorizeOwner()).
     */
    private function assertAdmin(): void
    {
        abort_unless(auth()->check() && auth()->user()->isAdmin(), 403, 'Bạn không có quyền truy cập khu vực quản trị.');
    }

    /**
     * Validate dữ liệu voucher — dùng chung cho cả tạo mới và cập nhật.
     */
    private function validateVoucher(Request $request, ?Voucher $voucher = null): array
    {
        $codeRule = 'required|string|max:50|unique:vouchers,code';
        if ($voucher) {
            $codeRule .= ',' . $voucher->id;
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'code'                 => $codeRule,
            'type'                 => 'required|in:percent,fixed',
            'value'                => 'required|numeric|min:0',
            'min_order_amount'     => 'nullable|numeric|min:0',
            'max_discount_amount'  => 'nullable|numeric|min:0',
            'usage_limit'          => 'nullable|integer|min:1',
            'start_date'           => 'nullable|date',
            'end_date'             => 'nullable|date|after_or_equal:start_date',
            'is_active'            => 'nullable|boolean',
        ]);

        // Giá trị giảm theo % không được vượt quá 100
        $validator->after(function ($validator) use ($request) {
            if ($request->input('type') === 'percent' && (float) $request->input('value') > 100) {
                $validator->errors()->add('value', 'Giảm theo % không được vượt quá 100.');
            }
        });

        // validate() tự động throw ValidationException nếu sai, Laravel tự
        // redirect về form cũ kèm lỗi + giữ nguyên dữ liệu đã nhập (giống $request->validate()).
        $validated = $validator->validate();

        $validated['code'] = strtoupper($validated['code']);
        $validated['min_order_amount'] = $validated['min_order_amount'] ?? 0;
        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}