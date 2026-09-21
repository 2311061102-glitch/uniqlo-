<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = CartService::currentCart($request);
        $cartItems = $cart?->items()->with('variant.product.images')->latest()->get() ?? collect();
        $subtotal = $cartItems->sum(fn ($item) => $item->subtotal);

        return view('cart.index', compact('cartItems', 'subtotal'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'quantity' => 'nullable|integer|min:1',
        ]);

        return $this->addVariant($request, ProductVariant::findOrFail($validated['product_variant_id']), $validated['quantity'] ?? 1);
    }

    public function add(Request $request, ProductVariant $variant)
    {
        $quantity = $request->validate(['quantity' => ['nullable', 'integer', 'min:1']])['quantity'] ?? 1;

        return $this->addVariant($request, $variant, $quantity);
    }

    private function addVariant(Request $request, ProductVariant $variant, int $quantity)
    {
        $cart = CartService::currentCart($request);
        $cartItem = $cart->items()->firstOrNew(['product_variant_id' => $variant->id]);
        $newQuantity = ($cartItem->exists ? $cartItem->quantity : 0) + $quantity;

        if ($newQuantity > $variant->stock_quantity) {
            return back()->with('error', 'Số lượng vượt quá tồn kho hiện có.');
        }

        $cartItem->quantity = $newQuantity;
        $cartItem->save();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'cart_count' => CartService::currentCart($request, false)?->totalQuantity() ?? 0]);
        }

        return back()->with('success', 'Đã thêm vào giỏ hàng!');
    }

    public function update(Request $request, CartItem $cartItem)
    {
        $this->authorizeOwner($request, $cartItem);
        $quantity = $request->validate(['quantity' => ['required', 'integer', 'min:1']])['quantity'];
        abort_if($quantity > $cartItem->variant->stock_quantity, 422, 'Số lượng vượt quá tồn kho hiện có.');
        $cartItem->update(['quantity' => $quantity]);

        return redirect()->route('cart.index')->with('success', 'Đã cập nhật giỏ hàng.');
    }

    public function destroy(Request $request, CartItem $cartItem)
    {
        $this->authorizeOwner($request, $cartItem);
        $cartItem->delete();

        return redirect()->route('cart.index')->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    public function clear(Request $request)
    {
        CartService::currentCart($request, false)?->items()->delete();

        return back()->with('success', 'Đã xóa toàn bộ giỏ hàng.');
    }

    private function authorizeOwner(Request $request, CartItem $cartItem): void
    {
        $cart = CartService::currentCart($request, false);
        abort_unless($cart && $cartItem->cart_id === $cart->id, 403, 'Bạn không có quyền thao tác với giỏ hàng này.');
    }

    private function authorizeItem(CartItem $item, Request $request): void
    {
        $cart = $item->cart;

        if (auth()->check()) {
            abort_unless($cart->user_id === auth()->id(), 403, 'Bạn không có quyền thao tác với giỏ hàng này.');
            return;
        }

        $guestToken = $request->cookie(CartService::GUEST_COOKIE_NAME);
        abort_unless($guestToken && $cart->guest_token === $guestToken, 403, 'Bạn không có quyền thao tác với giỏ hàng này.');
    }
    /**
     * Xử lý khi khách tick chọn 1 vài sản phẩm trong giỏ hàng rồi bấm
     * "Tiến hành thanh toán" — lưu lại danh sách ID đã chọn vào session,
     * để CheckoutController chỉ tính tiền/tạo đơn theo ĐÚNG các sản phẩm đó,
     * không phải toàn bộ giỏ hàng.
     */
    public function selectForCheckout(Request $request)
    {
        $request->validate([
            'selected_items'   => 'required|array|min:1',
            'selected_items.*' => 'integer',
        ], [
            'selected_items.required' => 'Vui lòng chọn ít nhất 1 sản phẩm để thanh toán.',
        ]);
 
        $cart = $this->getOrCreateCart($request);
 
        // BẢO MẬT: chỉ chấp nhận những cart_item_id THẬT SỰ thuộc giỏ hàng
        // của chính khách đang thao tác, phòng trường hợp tự sửa ID trên form.
        $validIds = $cart->items()
            ->whereIn('id', $request->selected_items)
            ->pluck('id')
            ->toArray();
 
        if (empty($validIds)) {
            return redirect()->route('cart.index')->with('error', 'Vui lòng chọn ít nhất 1 sản phẩm để thanh toán.');
        }
 
        session(['checkout.selected_item_ids' => $validIds]);
 
        return redirect()->route('checkout.index');
    }
}