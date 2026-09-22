<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\CartService;
use App\Services\ShippingFeeCalculator;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $cartItems = CartService::userCart($user)->items()->with('variant.product')->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng đang trống, không thể thanh toán.');
        }

        $addresses = $user->addresses()->orderByDesc('is_default')->get();

        if ($addresses->isEmpty()) {
            return redirect()->route('addresses.create')->with('error', 'Vui lòng thêm địa chỉ giao hàng trước khi thanh toán.');
        }

        $subtotal = $cartItems->sum(fn ($item) => $item->subtotal);
        $voucher = $this->sessionVoucher($subtotal);
        $availableVouchers = $this->availableVouchers($subtotal);
        $discount = $voucher?->calculateDiscount($subtotal) ?? 0;
        $selectedAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();
        $shippingFee = ShippingFeeCalculator::calculateForAddress($selectedAddress, $subtotal);
        $shippingDistance = ShippingFeeCalculator::distanceInKm($selectedAddress?->latitude, $selectedAddress?->longitude);
        $nearestBranch = ShippingFeeCalculator::nearestBranch($selectedAddress);
        $shippingFeesByAddress = $addresses->mapWithKeys(fn ($address) => [
            $address->id => ShippingFeeCalculator::calculateForAddress($address, $subtotal),
        ]);
        $nearestBranchesByAddress = $addresses->mapWithKeys(fn ($address) => [
            $address->id => ShippingFeeCalculator::nearestBranch($address),
        ]);
        $total = $subtotal + $shippingFee - $discount;

        return view('checkout.index', compact('cartItems', 'addresses', 'subtotal', 'voucher', 'availableVouchers', 'discount', 'shippingFee', 'shippingDistance', 'nearestBranch', 'shippingFeesByAddress', 'nearestBranchesByAddress', 'total'));
    }

    public function applyVoucher(Request $request)
    {
        $code = strtoupper(trim($request->validate(['voucher_code' => ['required', 'string', 'max:50']])['voucher_code']));
        $subtotal = CartService::userCart($request->user())->items()->with('variant')->get()->sum(fn ($item) => $item->subtotal);
        $voucher = Voucher::where('code', $code)->first();

        if (! $voucher) {
            return back()->with('error', 'Mã giảm giá không tồn tại.');
        }

        $check = $voucher->checkValidity($subtotal);
        if (! $check['valid']) {
            return back()->with('error', $check['message']);
        }

        session(['checkout.voucher_id' => $voucher->id]);

        return back()->with('success', 'Đã áp dụng mã giảm giá '.$voucher->code.'.');
    }

    public function removeVoucher()
    {
        session()->forget('checkout.voucher_id');

        return back()->with('success', 'Đã gỡ mã giảm giá.');
    }

    private function sessionVoucher(float $subtotal): ?Voucher
    {
        $voucherId = session('checkout.voucher_id');
        if (! $voucherId) {
            return null;
        }

        $voucher = Voucher::find($voucherId);
        if (! $voucher || ! $voucher->checkValidity($subtotal)['valid']) {
            session()->forget('checkout.voucher_id');
            return null;
        }

        return $voucher;
    }

    private function availableVouchers(float $subtotal)
    {
        $today = now()->toDateString();

        return Voucher::query()
            ->where('is_active', true)
            ->where(function ($query) use ($today) {
                $query->whereNull('start_date')->orWhere('start_date', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', $today);
            })
            ->where(function ($query) {
                $query->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->get()
            ->filter(fn (Voucher $voucher) => $voucher->checkValidity($subtotal)['valid'])
            ->sortByDesc(fn (Voucher $voucher) => $voucher->calculateDiscount($subtotal))
            ->values();
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'address_id' => ['required', 'exists:addresses,id'],
            'payment_method' => ['required', 'in:cod,vietqr,vnpay'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        if (! in_array($validated['payment_method'], ['cod', 'vietqr', 'vnpay'])) {
            return back()->with('error', 'Phương thức thanh toán này chưa khả dụng.');
        }

        $address = $user->addresses()->findOrFail($validated['address_id']);

        $cart = CartService::userCart($user);
        $cartItems = $cart->items()->with('variant.product')->get();

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
        $voucher = $this->sessionVoucher($subtotal);
        $discount = $voucher?->calculateDiscount($subtotal) ?? 0;
        $shippingFee = ShippingFeeCalculator::calculateForAddress($address, $subtotal);
        $nearestBranch = ShippingFeeCalculator::nearestBranch($address);
        $total = $subtotal + $shippingFee - $discount;

        $order = DB::transaction(function () use ($user, $address, $cart, $cartItems, $subtotal, $discount, $voucher, $shippingFee, $total, $validated, $nearestBranch) {
            $order = $user->orders()->create([
                'voucher_id' => $voucher?->id,
                'recipient_name' => $address->recipient_name,
                'recipient_phone' => $address->phone,
                'province' => $address->province,
                'district' => $address->district,
                'ward' => $address->ward,
                'address_detail' => $address->address_detail,
                'fulfillment_branch_code' => $nearestBranch['code'] ?? null,
                'fulfillment_branch_name' => $nearestBranch['name'] ?? null,
                'fulfillment_branch_address' => $nearestBranch['address'] ?? null,
                'subtotal_amount' => $subtotal,
                'shipping_fee' => $shippingFee,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'payment_method' => $validated['payment_method'],
                // COD không cần chờ duyệt thanh toán: đơn được xác nhận ngay,
                // tiền sẽ thu khi giao hàng. QR/VNPay chỉ xác nhận sau gateway callback/webhook.
                'payment_status' => $validated['payment_method'] === 'cod' ? 'unpaid' : 'pending',
                'order_status' => $validated['payment_method'] === 'cod' ? 'confirmed' : 'pending',
                'qr_expires_at' => $validated['payment_method'] === 'vietqr' ? now()->addMinutes(10) : null,
            ]);

            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_variant_id' => $item->variant->id,
                    'product_name' => $item->variant->product->name,
                    'variant_label' => "Size {$item->variant->size} - {$item->variant->color}",
                    'price' => $item->variant->final_price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                ]);

                $item->variant->decrement('stock_quantity', $item->quantity);
                $item->variant->product->increment('sold_count', $item->quantity);
            }

            $order->payments()->create([
                'payment_method' => $validated['payment_method'],
                'amount' => $total,
                'status' => 'pending',
            ]);

            if ($voucher) {
                $voucher->increment('used_count');
            }

            $cart->items()->delete();

            return $order;
        });

        if ($order->payment_method === 'vietqr') {
            return redirect()->route('payments.vietqr', $order);
        }

        if ($order->payment_method === 'vnpay') {
            return redirect()->route('payments.vnpay.pay', $order);
        }

        return redirect()->route('orders.show', $order)->with('success', 'Đặt hàng thành công!');
    }
}
