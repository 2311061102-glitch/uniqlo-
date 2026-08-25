<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\ProductVariant;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * GET /gio-hang — hiện toàn bộ sản phẩm trong giỏ của user đang đăng nhập.
     */
    public function index(Request $request)
    {
        $cartItems = $request->user()->cartItems()
            ->with('variant.product.images')
            ->latest()
            ->get();

        $subtotal = $cartItems->sum(fn ($item) => $item->subtotal);

        return view('cart.index', compact('cartItems', 'subtotal'));
    }

    /**
     * POST /gio-hang — thêm 1 biến thể (size+màu cụ thể) vào giỏ.
     * Nếu biến thể đó ĐÃ có trong giỏ, cộng dồn số lượng thay vì tạo dòng mới
     * (nhờ ràng buộc unique(user_id, product_variant_id) đã đặt ở Giai đoạn 1).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_variant_id' => ['required', 'exists:product_variants,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $variant = ProductVariant::findOrFail($validated['product_variant_id']);
        $quantityToAdd = $validated['quantity'] ?? 1;

        $cartItem = $request->user()->cartItems()->firstOrNew([
            'product_variant_id' => $variant->id,
        ]);

        $newQuantity = ($cartItem->exists ? $cartItem->quantity : 0) + $quantityToAdd;

        // Không cho thêm vượt quá số lượng tồn kho hiện có của ĐÚNG biến thể này
        if ($newQuantity > $variant->stock_quantity) {
            return back()->with('error', 'Số lượng vượt quá tồn kho hiện có (còn '.$variant->stock_quantity.' sản phẩm).');
        }

        $cartItem->user_id = $request->user()->id;
        $cartItem->quantity = $newQuantity;
        $cartItem->save();

        return back()->with('success', 'Đã thêm vào giỏ hàng!');
    }

    /**
     * PUT /gio-hang/{cartItem} — đổi số lượng của 1 dòng trong giỏ.
     */
    public function update(Request $request, CartItem $cartItem)
    {
        $this->authorizeOwner($cartItem);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($validated['quantity'] > $cartItem->variant->stock_quantity) {
            return back()->with('error', 'Số lượng vượt quá tồn kho hiện có (còn '.$cartItem->variant->stock_quantity.' sản phẩm).');
        }

        $cartItem->update(['quantity' => $validated['quantity']]);

        return back()->with('success', 'Đã cập nhật số lượng.');
    }

    /**
     * DELETE /gio-hang/{cartItem} — xóa 1 sản phẩm khỏi giỏ.
     */
    public function destroy(CartItem $cartItem)
    {
        $this->authorizeOwner($cartItem);

        $cartItem->delete();

        return back()->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    /**
     * Chặn user A thao tác vào giỏ hàng của user B (giống pattern IDOR ở sổ địa chỉ).
     */
    private function authorizeOwner(CartItem $cartItem): void
    {
        abort_if($cartItem->user_id !== auth()->id(), 403, 'Bạn không có quyền thao tác với giỏ hàng này.');
    }
}
