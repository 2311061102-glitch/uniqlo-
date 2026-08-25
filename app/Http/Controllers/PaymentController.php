<?php

namespace App\Http\Controllers;

use App\Models\Order;

class PaymentController extends Controller
{
    /**
     * GET /don-hang/{order}/thanh-toan-vietqr — hiện mã QR chuyển khoản ngân hàng THẬT.
     *
     * Dùng "VietQR Quick Link" — API công khai, MIỄN PHÍ, không cần đăng ký tài khoản
     * hay xin API key. Chỉ cần ghép đúng URL theo cú pháp của VietQR.io là ra ảnh QR
     * chứa sẵn: ngân hàng, số tài khoản, số tiền, nội dung chuyển khoản.
     */
    public function vietqr(Order $order)
    {
        abort_if($order->user_id !== auth()->id(), 403, 'Bạn không có quyền xem đơn hàng này.');
        abort_unless($order->payment_method === 'vietqr', 404);

        $qrUrl = sprintf(
            'https://img.vietqr.io/image/%s-%s-compact2.png?amount=%d&addInfo=%s&accountName=%s',
            config('services.vietqr.bank_bin'),
            config('services.vietqr.account_no'),
            $order->total_amount,
            urlencode($order->order_code),
            urlencode((string) config('services.vietqr.account_name'))
        );

        return view('payments.vietqr', compact('order', 'qrUrl'));
    }
}
