<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders()->latest()->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $this->authorizeAccess($order);

        $order->load('items', 'payments');

        return view('orders.show', compact('order'));
    }

    public function tracking(Order $order)
    {
        $this->authorizeAccess($order);

        $steps = [
            ['status' => 'pending', 'title' => 'Đã đặt hàng', 'description' => 'Đơn hàng đã được ghi nhận.'],
            ['status' => 'confirmed', 'title' => 'Đã xác nhận', 'description' => 'Cửa hàng đã xác nhận và chuẩn bị đơn hàng.'],
            ['status' => 'shipping', 'title' => 'Đang giao hàng', 'description' => 'Đơn hàng đang trên đường đến bạn.'],
            ['status' => 'completed', 'title' => 'Đã giao hàng', 'description' => 'Đơn hàng đã được giao thành công.'],
        ];

        $statusOrder = ['pending' => 0, 'confirmed' => 1, 'shipping' => 2, 'completed' => 3];
        $currentIndex = $statusOrder[$order->order_status] ?? 0;

        return view('orders.tracking', compact('order', 'steps', 'currentIndex'));
    }

    public function cancel(Order $order)
    {
        $this->authorizeAccess($order);

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
     * POST /don-hang/{order}/xac-nhan-thanh-toan — CHỈ ADMIN dùng được (kiểm tra ở
     * cả middleware route 'role:admin' LẪN ở đây — 2 lớp phòng thủ, xem giải thích
     * trong file hướng dẫn). Dùng để xác nhận đã nhận được tiền chuyển khoản VietQR,
     * vì VietQR bản miễn phí không tự động báo về khi có tiền vào — cần người kiểm
     * tra thủ công qua app ngân hàng rồi bấm xác nhận ở đây.
     *
     * LƯU Ý: đây là giải pháp tạm thời cho tới khi Thành viên 4 làm xong trang Admin
     * thật — lúc đó nên chuyển hành động này vào đúng trang quản lý đơn hàng của admin.
     */
    public function confirmPayment(Order $order)
    {
        abort_unless(auth()->user()->isAdmin(), 403, 'Chỉ quản trị viên mới có quyền xác nhận thanh toán.');

        $order->update([
            'payment_status' => 'paid',
            'order_status' => $order->order_status === 'pending' ? 'confirmed' : $order->order_status,
        ]);

        $order->payments()->latest()->first()?->update([
            'status' => 'success',
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Đã xác nhận thanh toán cho đơn hàng '.$order->order_code.'.');
    }

    /**
     * Cho phép xem đơn nếu: là chủ đơn hàng, HOẶC là admin (admin cần xem để xác nhận
     * thanh toán VietQR thủ công ở giai đoạn này).
     */
    private function authorizeAccess(Order $order): void
    {
        $user = auth()->user();

        abort_if($order->user_id !== $user->id && ! $user->isAdmin(), 403, 'Bạn không có quyền xem đơn hàng này.');
    }
}
