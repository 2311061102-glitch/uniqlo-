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
    /**
     * Lấy (hoặc tạo mới) giỏ hàng của người dùng hiện tại.
     * - Đã đăng nhập -> giỏ hàng gắn với user_id.
     * - Chưa đăng nhập -> nhận diện qua guest_token lưu trong Cookie.
     */
    private function getOrCreateCart(Request $request): Cart
    {
        if (auth()->check()) {
            return Cart::firstOrCreate(['user_id' => auth()->id()]);
        }

        $guestToken = $request->cookie(CartService::GUEST_COOKIE_NAME);

        if ($guestToken) {
            $cart = Cart::where('guest_token', $guestToken)->first();
            if ($cart) {
                return $cart;
            }
        }

        $guestToken = (string) Str::uuid();
        $cart = Cart::create(['guest_token' => $guestToken]);

        Cookie::queue(CartService::GUEST_COOKIE_NAME, $guestToken, 30 * 24 * 60);

        return $cart;
    }

    /**
     * Trang giỏ hàng.
     */
    public function index(Request $request)
    {
        $cart = $this->getOrCreateCart($request);
        $cart->load('items.variant.product', 'items.variant.images');

        return view('cart.index', compact('cart'));
    }

    /**
     * Thêm 1 biến thể sản phẩm (size+màu cụ thể) vào giỏ.
     * Được gọi từ nút "Thêm vào giỏ" ở trang chi tiết sản phẩm (sau khi đã chọn size/màu),
     * hoặc gọi qua fetch() bằng JS -> trả JSON nếu request là AJAX.
     */
    public function add(Request $request, ProductVariant $variant)
    {
        $request->validate([
            'quantity' => 'nullable|integer|min:1',
        ]);

        $quantity = $request->input('quantity', 1);
        $cart = $this->getOrCreateCart($request);

        $existingItem = CartItem::where('cart_id', $cart->id)
            ->where('product_variant_id', $variant->id)
            ->first();

        $newQuantity = ($existingItem->quantity ?? 0) + $quantity;

        if ($newQuantity > $variant->stock_quantity) {
            $message = 'Số lượng vượt quá tồn kho. Chỉ còn ' . $variant->stock_quantity . ' sản phẩm.';

            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return redirect()->back()->with('error', $message);
        }

        if ($existingItem) {
            $existingItem->update(['quantity' => $newQuantity]);
        } else {
            CartItem::create([
                'cart_id'            => $cart->id,
                'product_variant_id' => $variant->id,
                'quantity'           => $quantity,
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success'      => true,
                'message'      => 'Đã thêm vào giỏ hàng.',
                'cart_count'   => $cart->fresh()->items->sum('quantity'),
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Đã thêm sản phẩm vào giỏ hàng.');
    }

    /**
     * Cập nhật số lượng 1 dòng trong giỏ hàng.
     */
    public function update(Request $request, CartItem $item)
    {
        $this->authorizeItem($item, $request);

        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        if ($request->quantity > $item->variant->stock_quantity) {
            return redirect()->route('cart.index')
                ->with('error', 'Số lượng vượt quá tồn kho. Chỉ còn ' . $item->variant->stock_quantity . ' sản phẩm.');
        }

        $item->update(['quantity' => $request->quantity]);

        return redirect()->route('cart.index')->with('success', 'Đã cập nhật giỏ hàng.');
    }

    /**
     * Xóa 1 sản phẩm khỏi giỏ hàng.
     */
    public function remove(Request $request, CartItem $item)
    {
        $this->authorizeItem($item, $request);

        $item->delete();

        return redirect()->route('cart.index')->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    /**
     * Xóa toàn bộ giỏ hàng.
     */
    public function clear(Request $request)
    {
        $cart = $this->getOrCreateCart($request);
        $cart->items()->delete();

        return redirect()->route('cart.index')->with('success', 'Đã xóa toàn bộ giỏ hàng.');
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