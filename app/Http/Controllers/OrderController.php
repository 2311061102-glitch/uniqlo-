<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * GET /don-hang — danh sách đơn hàng của user đang đăng nhập.
     */
    public function index(Request $request)
    {
        $orders = $request->user()->orders()->latest()->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * GET /don-hang/{order} — chi tiết 1 đơn hàng.
     */
    public function show(Order $order)
    {
        $this->authorizeOwner($order);

        $order->load('items', 'payments');

        return view('orders.show', compact('order'));
    }

    /**
     * POST /don-hang/{order}/huy — hủy đơn, chỉ cho phép khi đơn còn ở trạng thái "pending"
     * (chưa được nhân viên xác nhận xử lý). Hoàn lại tồn kho đã trừ lúc đặt hàng.
     */
    public function cancel(Order $order)
    {
        $this->authorizeOwner($order);

        if ($order->order_status !== 'pending') {
            return back()->with('error', 'Đơn hàng đang được xử lý, không thể hủy.');
        }

        foreach ($order->items as $item) {
            $item->variant?->increment('stock_quantity', $item->quantity);
        }

        $order->update(['order_status' => 'cancelled']);

        return back()->with('success', 'Đã hủy đơn hàng.');
    }

    /**
     * Chặn user A xem/hủy đơn hàng của user B (giống pattern IDOR ở sổ địa chỉ/giỏ hàng).
     */
    private function authorizeOwner(Order $order): void
    {
        abort_if($order->user_id !== auth()->id(), 403, 'Bạn không có quyền xem đơn hàng này.');
    }
}
