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
        $cart = Cart::with('items.variant.product')->firstOrCreate(['user_id' => auth()->id()]);

        $cartItems = CartService::userCart($user)->items()->with('variant.product')->get();

        if (! $selectedItems) {
            return redirect()->route('cart.index')->with('error', 'Vui lòng chọn sản phẩm cần thanh toán từ giỏ hàng.');
        }

        $addresses = auth()->user()->addresses;

        if ($addresses->isEmpty()) {
            return redirect()
                ->route('addresses.create')
                ->with('error', 'Bạn cần thêm ít nhất 1 địa chỉ nhận hàng trước khi thanh toán.');
        }

        $subtotal = $cartItems->sum(fn ($item) => $item->subtotal);
        $voucher = $this->sessionVoucher($subtotal);
        $availableVouchers = $this->availableVouchers($subtotal);
        $discount = $voucher?->calculateDiscount($subtotal) ?? 0;
        $selectedAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();
        $shippingFee = ShippingFeeCalculator::calculateForAddress($selectedAddress, $subtotal);
        $shippingDistance = ShippingFeeCalculator::distanceInKm($selectedAddress?->latitude, $selectedAddress?->longitude);
        $shippingFeesByAddress = $addresses->mapWithKeys(fn ($address) => [
            $address->id => ShippingFeeCalculator::calculateForAddress($address, $subtotal),
        ]);
        $total = $subtotal + $shippingFee - $discount;

        return view('checkout.index', compact('cartItems', 'addresses', 'subtotal', 'voucher', 'availableVouchers', 'discount', 'shippingFee', 'shippingDistance', 'shippingFeesByAddress', 'total'));
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

    public function applyVoucher(Request $request)
    {
        $request->validate(['voucher_code' => 'required|string']);

        $cart = Cart::with('items.variant')->firstOrCreate(['user_id' => auth()->id()]);
        $selectedItems = $this->resolveSelectedItems($cart);

        if (! $selectedItems) {
            return redirect()->route('cart.index')->with('error', 'Vui lòng chọn sản phẩm cần thanh toán từ giỏ hàng.');
        }

        $subtotal = $selectedItems->sum(fn ($item) => $item->variant->final_price * $item->quantity);

        $voucher = Voucher::where('code', $request->voucher_code)->first();

        if (!$voucher) {
            return redirect()->route('checkout.index')->with('error', 'Mã giảm giá không tồn tại.');
        }

        $check = $voucher->checkValidity($subtotal);
        if (!$check['valid']) {
            return redirect()->route('checkout.index')->with('error', $check['message']);
        }

        session(['checkout.voucher_id' => $voucher->id]);

        return redirect()->route('checkout.index')->with('success', 'Áp dụng mã giảm giá thành công.');
    }

    public function removeVoucher()
    {
        session()->forget('checkout.voucher_id');
        return redirect()->route('checkout.index')->with('success', 'Đã gỡ mã giảm giá.');
    }

    public function updateRegion(Request $request)
    {
        $request->validate(['shipping_region' => 'required|in:inner_city,outer_city,other']);
        session(['checkout.region' => $request->shipping_region]);
        return redirect()->route('checkout.index');
    }

    public function updateAddress(Request $request)
    {
        $request->validate(['address_id' => 'required|exists:addresses,id']);

        $address = Address::findOrFail($request->address_id);
        abort_unless($address->user_id === auth()->id(), 403);

        session(['checkout.address_id' => $address->id]);
        return redirect()->route('checkout.index');
    }

    /**
     * Xử lý đặt hàng: tạo Order + OrderItem + Payment, trừ tồn kho biến thể,
     * xóa ĐÚNG các dòng đã chọn khỏi giỏ hàng (giữ lại phần chưa chọn).
     */
    public function store(Request $request)
    {
        $request->validate([
            'address_id'        => 'required|exists:addresses,id',
            'shipping_region'   => 'required|in:inner_city,outer_city,other',
            'payment_method'    => 'required|in:cod,bank_transfer,qr',
        ]);

        $address = Address::findOrFail($request->address_id);
        abort_unless($address->user_id === auth()->id(), 403, 'Địa chỉ không hợp lệ.');

        $cart = Cart::with('items.variant.product')->firstOrCreate(['user_id' => auth()->id()]);

        $selectedItems = $this->resolveSelectedItems($cart);

        if (! $selectedItems) {
            return redirect()->route('cart.index')->with('error', 'Vui lòng chọn sản phẩm cần thanh toán từ giỏ hàng.');
        }

        try {
            $order = DB::transaction(function () use ($request, $selectedItems, $address) {

        $cart = CartService::userCart($user);
        $cartItems = $cart->items()->with('variant.product')->get();

                    if (!$variant) {
                        throw new \Exception('Một sản phẩm trong giỏ hàng không còn tồn tại.');
                    }
                    if ($item->quantity > $variant->stock_quantity) {
                        throw new \Exception(
                            'Sản phẩm "' . $variant->product->name . '" (' . $variant->size . ' - ' . $variant->color . ') không đủ tồn kho.'
                        );
                    }

                    $lockedVariants[$item->id] = $variant;
                }

        $subtotal = $cartItems->sum(fn ($item) => $item->subtotal);
        $voucher = $this->sessionVoucher($subtotal);
        $discount = $voucher?->calculateDiscount($subtotal) ?? 0;
        $shippingFee = ShippingFeeCalculator::calculateForAddress($address, $subtotal);
        $total = $subtotal + $shippingFee - $discount;

        $order = DB::transaction(function () use ($user, $address, $cart, $cartItems, $subtotal, $discount, $voucher, $shippingFee, $total, $validated) {
            $order = $user->orders()->create([
                'voucher_id' => $voucher?->id,
                'recipient_name' => $address->recipient_name,
                'recipient_phone' => $address->phone,
                'province' => $address->province,
                'district' => $address->district,
                'ward' => $address->ward,
                'address_detail' => $address->address_detail,
                'subtotal_amount' => $subtotal,
                'shipping_fee' => $shippingFee,
                'discount_amount' => $discount,
                'total_amount' => $total,
                'payment_method' => $validated['payment_method'],
                'payment_status' => 'pending',
                'order_status' => 'pending',
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

                // 4. Tạo OrderItem + trừ tồn kho — CHỈ với các biến thể ĐÃ CHỌN
                foreach ($selectedItems as $item) {
                    $variant = $lockedVariants[$item->id];

            $order->payments()->create([
                'payment_method' => $validated['payment_method'],
                'amount' => $total,
                'status' => 'pending',
            ]);

            if ($voucher) {
                $voucher->increment('used_count');
            }

            $cart->items()->delete();

        return redirect()->route('checkout.success', $order)->with('success', 'Thanh toán đã được ghi nhận.');
    }

    public function success(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 403);

        return view('checkout.success', compact('order'));
    }

    public function checkPaymentStatus(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 403);

        $this->cancelIfExpired($order);

        return response()->json([
            'payment_status' => $order->payment_status,
            'expired'        => $order->order_status === 'cancelled' && $order->payment_status !== 'paid',
        ]);
    }

    /**
     * Nếu đơn QR/Chuyển khoản đã quá 10 phút mà vẫn chưa thanh toán -> tự động
     * hủy đơn và hoàn lại tồn kho đã trừ lúc đặt hàng.
     */
    private function cancelIfExpired(Order $order): void
    {
        $isExpired = $order->qr_expires_at
            && now()->gt($order->qr_expires_at)
            && $order->payment_status === 'unpaid'
            && $order->order_status !== 'cancelled';

        if (! $isExpired) {
            return;
        }

        DB::transaction(function () use ($order) {
            $order->load('items.variant');

            foreach ($order->items as $item) {
                if ($item->variant) {
                    $item->variant->increment('stock_quantity', $item->quantity);
                }
            }

            $order->update(['order_status' => 'cancelled']);
        });

        $order->refresh();
    }

    /**
     * Lấy ra đúng các dòng giỏ hàng mà khách đã TICK CHỌN ở trang /gio-hang
     * (lưu tạm trong session bởi CartController::selectForCheckout()).
     * Trả về null nếu chưa chọn gì / chọn item không còn tồn tại trong giỏ nữa.
     */
    private function resolveSelectedItems(Cart $cart)
    {
        $selectedIds = session('checkout.selected_item_ids', []);

        if (empty($selectedIds)) {
            return null;
        }

        $items = $cart->items->whereIn('id', $selectedIds);

        return $items->isEmpty() ? null : $items;
    }
}