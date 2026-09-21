<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Danh sách đơn hàng của khách đang đăng nhập.
     */
    public function index()
    {
        $orders = auth()->user()->orders()->latest()->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Chi tiết 1 đơn hàng — chủ đơn hoặc admin mới xem được.
     */
    public function show(Order $order)
    {
        abort_unless(
            $order->user_id === auth()->id() || auth()->user()->isAdmin(),
            403,
            'Bạn không có quyền xem đơn hàng này.'
        );

        $order->load('items.variant.product', 'voucher', 'payment');

        return view('orders.show', compact('order'));
    }

    /**
     * Hủy đơn hàng — chỉ khi đang ở trạng thái pending, hoàn lại tồn kho đã trừ.
     */
    public function cancel(Order $order)
    {
        abort_unless($order->user_id === auth()->id(), 403, 'Bạn không có quyền hủy đơn hàng này.');

        if (!$order->canBeCancelled()) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Đơn hàng này không thể hủy (đã được xử lý).');
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                if ($item->variant) {
                    $item->variant->increment('stock_quantity', $item->quantity);
                }
            }

            $order->update(['order_status' => 'cancelled']);
        });

        return redirect()->route('orders.show', $order)->with('success', 'Đã hủy đơn hàng thành công.');
    }
}