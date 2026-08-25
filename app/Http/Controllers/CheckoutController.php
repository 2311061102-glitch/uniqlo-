<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    private const SHIPPING_FEE = 30000;

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

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'address_id' => ['required', 'exists:addresses,id'],
            'payment_method' => ['required', 'in:cod,vietqr,momo,vnpay'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        // Giai đoạn này: COD và VietQR đã hoạt động thật. MoMo/VNPay còn chờ giai đoạn sau.
        if (! in_array($validated['payment_method'], ['cod', 'vietqr'])) {
            return back()->with('error', 'Phương thức thanh toán này chưa khả dụng.');
        }

        $address = $user->addresses()->findOrFail($validated['address_id']);

        $cartItems = $user->cartItems()->with('variant.product')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng đang trống.');
        }

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

                $item->variant->decrement('stock_quantity', $item->quantity);
                $item->variant->product->increment('sold_count', $item->quantity);
            }

            $order->payments()->create([
                'method' => $validated['payment_method'],
                'amount' => $total,
                'status' => 'pending',
            ]);

            $user->cartItems()->delete();

            return $order;
        });

        // VietQR: đưa khách sang trang hiện mã QR để chuyển khoản, thay vì thẳng tới trang chi tiết đơn
        if ($order->payment_method === 'vietqr') {
            return redirect()->route('payments.vietqr', $order);
        }

        return redirect()->route('orders.show', $order)->with('success', 'Đặt hàng thành công!');
    }
}
