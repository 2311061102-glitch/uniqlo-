<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    private const SHIPPING_FEE = 30000;

    /**
     * GET /thanh-toan — hiện trang checkout: chọn địa chỉ, chọn phương thức thanh toán.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $cartItems = $user->cartItems()->with('variant.product')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng đang trống, không thể thanh toán.');
        }

        $addresses = $user->addresses()->orderByDesc('is_default')->get();

        if ($addresses->isEmpty()) {
            return redirect()->route('addresses.create')->with('error', 'Vui lòng thêm địa chỉ giao hàng trước khi thanh toán.');
        }

        $subtotal = $cartItems->sum(fn ($item) => $item->subtotal);
        $shippingFee = self::SHIPPING_FEE;
        $total = $subtotal + $shippingFee;

        return view('checkout.index', compact('cartItems', 'addresses', 'subtotal', 'shippingFee', 'total'));
    }

    /**
     * POST /thanh-toan — tạo đơn hàng thật, trừ tồn kho, xóa giỏ hàng.
     */
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'address_id' => ['required', 'exists:addresses,id'],
            'payment_method' => ['required', 'in:cod,vietqr,momo,vnpay'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        // Ở giai đoạn này chỉ COD hoạt động thật — 3 phương thức kia sẽ mở dần ở các giai đoạn sau
        if ($validated['payment_method'] !== 'cod') {
            return back()->with('error', 'Phương thức thanh toán này chưa khả dụng, vui lòng chọn COD.');
        }

        // findOrFail qua $user->addresses() (không phải Address::findOrFail) để tự động
        // đảm bảo địa chỉ này THUỘC ĐÚNG user đang đặt hàng, chặn IDOR ngay từ đây
        $address = $user->addresses()->findOrFail($validated['address_id']);

        $cartItems = $user->cartItems()->with('variant.product')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng đang trống.');
        }

        // Kiểm tra lại tồn kho LẦN CUỐI trước khi đặt hàng — phòng trường hợp
        // tồn kho đã thay đổi kể từ lúc khách thêm vào giỏ (người khác cũng đang mua)
        foreach ($cartItems as $item) {
            if ($item->quantity > $item->variant->stock_quantity) {
                return redirect()->route('cart.index')->with(
                    'error',
                    "Sản phẩm \"{$item->variant->product->name}\" (size {$item->variant->size}, {$item->variant->color}) không đủ tồn kho."
                );
            }
        }

        $subtotal = $cartItems->sum(fn ($item) => $item->subtotal);
        $shippingFee = self::SHIPPING_FEE;
        $total = $subtotal + $shippingFee;

        /*
         * DB::transaction: đảm bảo TẤT CẢ các bước bên trong HOẶC làm hết,
         * HOẶC không làm gì cả. Nếu có lỗi ở bất kỳ bước nào (VD lỗi khi trừ
         * tồn kho), toàn bộ thay đổi trước đó trong cùng transaction sẽ tự
         * động ROLLBACK (hủy bỏ) — tránh trường hợp tạo đơn hàng xong nhưng
         * không trừ được tồn kho, hoặc trừ tồn kho xong nhưng đơn lại lỗi.
         */
        $order = DB::transaction(function () use ($user, $address, $cartItems, $subtotal, $shippingFee, $total, $validated) {
            $order = $user->orders()->create([
                'recipient_name' => $address->recipient_name,
                'phone' => $address->phone,
                'province' => $address->province,
                'district' => $address->district,
                'ward' => $address->ward,
                'address_detail' => $address->address_detail,
                'subtotal' => $subtotal,
                'shipping_fee' => $shippingFee,
                'total_amount' => $total,
                'payment_method' => $validated['payment_method'],
                // COD chỉ coi là "đã thanh toán" khi nhân viên xác nhận đã thu tiền lúc giao hàng
                'payment_status' => 'pending',
                'order_status' => 'pending',
                'note' => $validated['note'] ?? null,
            ]);

            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_id' => $item->variant->product_id,
                    'product_variant_id' => $item->variant->id,
                    'product_name' => $item->variant->product->name,
                    'variant_size' => $item->variant->size,
                    'variant_color' => $item->variant->color,
                    'unit_price' => $item->variant->final_price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                ]);

                // Trừ tồn kho THẬT ngay khi đặt hàng thành công
                $item->variant->decrement('stock_quantity', $item->quantity);

                // Cộng dồn số lượng đã bán — dùng cho sắp xếp "bán chạy" ở trang danh sách sản phẩm
                $item->variant->product->increment('sold_count', $item->quantity);
            }

            $order->payments()->create([
                'method' => $validated['payment_method'],
                'amount' => $total,
                'status' => 'pending',
            ]);

            // Đặt hàng xong thì xóa sạch giỏ hàng của user này
            $user->cartItems()->delete();

            return $order;
        });

        return redirect()->route('orders.show', $order)->with('success', 'Đặt hàng thành công!');
    }
}
