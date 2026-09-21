<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\Request;

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
        $validated = $request->validate([
            'product_variant_id' => ['required', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
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

        return back()->with('success', 'Đã cập nhật số lượng.');
    }

    public function destroy(Request $request, CartItem $cartItem)
    {
        $this->authorizeOwner($request, $cartItem);
        $cartItem->delete();

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
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
}
