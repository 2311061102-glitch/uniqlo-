<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\Voucher;
use App\Services\ShippingFeeCalculator;
use App\Services\VietQrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    /**
     * Trang checkout: chọn địa chỉ đã lưu, khu vực giao hàng, mã giảm giá, phương thức thanh toán.
     */
    public function index()
    {
        $cart = Cart::with('items.variant.product')->firstOrCreate(['user_id' => auth()->id()]);

        $selectedItems = $this->resolveSelectedItems($cart);

        if (! $selectedItems) {
            return redirect()->route('cart.index')->with('error', 'Vui lòng chọn sản phẩm cần thanh toán từ giỏ hàng.');
        }

        $addresses = auth()->user()->addresses;

        if ($addresses->isEmpty()) {
            return redirect()
                ->route('addresses.create')
                ->with('error', 'Bạn cần thêm ít nhất 1 địa chỉ nhận hàng trước khi thanh toán.');
        }

        $subtotal = $selectedItems->sum(fn ($item) => $item->variant->final_price * $item->quantity);

        $voucher = null;
        $discount = 0;
        if (session()->has('checkout.voucher_id')) {
            $voucher = Voucher::find(session('checkout.voucher_id'));
            if ($voucher) {
                $check = $voucher->checkValidity($subtotal);
                if ($check['valid']) {
                    $discount = $voucher->calculateDiscount($subtotal);
                } else {
                    session()->forget('checkout.voucher_id');
                    $voucher = null;
                }
            }
        }

        $regionOptions = ShippingFeeCalculator::regionOptions();
        $selectedRegion = session('checkout.region', 'inner_city');
        $shippingFee = ShippingFeeCalculator::calculate($selectedRegion, $subtotal);

        $selectedAddressId = session('checkout.address_id', $addresses->firstWhere('is_default', true)?->id ?? $addresses->first()->id);

        $total = $subtotal + $shippingFee - $discount;

        return view('checkout.index', compact(
            'selectedItems', 'subtotal', 'voucher', 'discount', 'addresses', 'selectedAddressId',
            'regionOptions', 'selectedRegion', 'shippingFee', 'total'
        ));
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

                // 1. Khóa từng biến thể, kiểm tra tồn kho thật
                $lockedVariants = [];
                foreach ($selectedItems as $item) {
                    $variant = ProductVariant::lockForUpdate()->find($item->product_variant_id);

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

                // 2. Tính lại toàn bộ tiền từ dữ liệu THẬT trong Database
                $subtotal = 0;
                foreach ($selectedItems as $item) {
                    $subtotal += $lockedVariants[$item->id]->final_price * $item->quantity;
                }

                $shippingFee = ShippingFeeCalculator::calculate($request->shipping_region, $subtotal);

                $voucher = null;
                $discount = 0;
                if (session()->has('checkout.voucher_id')) {
                    $voucher = Voucher::lockForUpdate()->find(session('checkout.voucher_id'));
                    if ($voucher) {
                        $check = $voucher->checkValidity($subtotal);
                        if (!$check['valid']) {
                            throw new \Exception($check['message']);
                        }
                        $discount = $voucher->calculateDiscount($subtotal);
                    }
                }

                $total = $subtotal + $shippingFee - $discount;

                // 3. Tạo Order — sao chép (snapshot) toàn bộ địa chỉ tại thời điểm đặt hàng
                $order = Order::create([
                    'order_code'        => 'ORD' . now()->format('YmdHis') . rand(10, 99),
                    'user_id'           => auth()->id(),
                    'voucher_id'        => $voucher?->id,
                    'address_id'        => $address->id,
                    'recipient_name'    => $address->recipient_name,
                    'recipient_phone'   => $address->phone,
                    'province'          => $address->province,
                    'district'          => $address->district,
                    'ward'              => $address->ward,
                    'address_detail'    => $address->address_detail,
                    'subtotal_amount'   => $subtotal,
                    'shipping_fee'      => $shippingFee,
                    'discount_amount'   => $discount,
                    'total_amount'      => $total,
                    'payment_method'    => $request->payment_method,
                    'payment_status'    => 'unpaid',
                    'order_status'      => 'pending',
                    // QR/Chuyển khoản chỉ có hiệu lực trong 10 phút. COD không cần nên để null.
                    'qr_expires_at'     => in_array($request->payment_method, ['qr', 'bank_transfer'])
                        ? now()->addMinutes(10)
                        : null,
                ]);

                // 4. Tạo OrderItem + trừ tồn kho — CHỈ với các biến thể ĐÃ CHỌN
                foreach ($selectedItems as $item) {
                    $variant = $lockedVariants[$item->id];

                    $order->items()->create([
                        'product_variant_id' => $variant->id,
                        'product_name'       => $variant->product->name,
                        'variant_label'      => $variant->size . ' - ' . $variant->color,
                        'price'              => $variant->final_price,
                        'quantity'           => $item->quantity,
                        'subtotal'           => $variant->final_price * $item->quantity,
                    ]);

                    $variant->decrement('stock_quantity', $item->quantity);
                }

                // 5. Tạo bản ghi Payment
                Payment::create([
                    'order_id'       => $order->id,
                    'payment_method' => $request->payment_method,
                    'amount'         => $total,
                    'status'         => 'pending',
                ]);

                if ($voucher) {
                    $voucher->increment('used_count');
                }

                return $order;
            });
        } catch (\Exception $e) {
            return redirect()->route('checkout.index')->with('error', $e->getMessage());
        }

        // Chỉ xóa ĐÚNG các dòng đã chọn khỏi giỏ hàng, giữ lại phần chưa chọn
        CartItem::whereIn('id', $selectedItems->pluck('id'))->delete();

        session()->forget([
            'checkout.voucher_id',
            'checkout.region',
            'checkout.address_id',
            'checkout.selected_item_ids',
        ]);

        if (in_array($order->payment_method, ['qr', 'bank_transfer'])) {
            return redirect()->route('checkout.payment', $order);
        }

        return redirect()->route('checkout.success', $order);
    }

    public function payment(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 403);

        if ($order->payment_method === 'cod') {
            return redirect()->route('checkout.success', $order);
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('checkout.success', $order);
        }

        $this->cancelIfExpired($order);

        if ($order->order_status === 'cancelled') {
            return redirect()->route('cart.index')
                ->with('error', 'Mã QR của đơn hàng đã hết hạn (quá 10 phút chưa thanh toán). Đơn hàng đã bị hủy, vui lòng đặt lại.');
        }

        $order->load('payment');

        $qrImageUrl = VietQrService::buildImageUrl($order->order_code, $order->total_amount);

        return view('checkout.payment', compact('order', 'qrImageUrl'));
    }

    public function confirmPayment(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 403, 'Bạn không có quyền xác nhận đơn hàng này.');

        if ($order->payment_status !== 'paid') {
            $order->payment()->update([
                'status'           => 'success',
                'transaction_code' => 'TXN' . now()->format('YmdHis') . rand(100, 999),
                'paid_at'          => now(),
            ]);

            $order->update(['payment_status' => 'paid']);
        }

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