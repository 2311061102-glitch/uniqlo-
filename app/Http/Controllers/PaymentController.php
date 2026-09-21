<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MoMoService;
use App\Services\VnPayService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
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

    /**
     * GET /don-hang/{order}/thanh-toan-momo — tạo link thanh toán MoMo rồi
     * CHUYỂN HƯỚNG khách sang thẳng trang thanh toán của MoMo (redirect ra ngoài web).
     */
    public function momo(Order $order, MoMoService $momoService)
    {
        abort_if($order->user_id !== auth()->id(), 403, 'Bạn không có quyền thao tác với đơn hàng này.');
        abort_unless($order->payment_method === 'momo', 404);

        $payUrl = $momoService->createPaymentUrl($order);

        if (! $payUrl) {
            return redirect()->route('orders.show', $order)
                ->with('error', 'Không tạo được liên kết thanh toán MoMo, vui lòng thử lại.');
        }

        return redirect()->away($payUrl);
    }

    public function vnpay(Order $order, Request $request, VnPayService $vnpay)
    {
        abort_if($order->user_id !== auth()->id(), 403, 'Bạn không có quyền thanh toán đơn hàng này.');
        abort_unless($order->payment_method === 'vnpay', 404);

        try {
            return redirect()->away($vnpay->createPaymentUrl($order, $request));
        } catch (\RuntimeException $exception) {
            $order->payments()->where('method', 'vnpay')->latest()->first()?->update([
                'status' => 'failed',
                'gateway_response' => ['error' => $exception->getMessage()],
            ]);

            return redirect()->route('orders.show', $order)->with('error', $exception->getMessage());
        }
    }

    public function vnpayReturn(Request $request, VnPayService $vnpay)
    {
        if (! $vnpay->verifyResponse($request)) {
            return redirect()->route('orders.index')->with('error', 'Chữ ký VNPay không hợp lệ.');
        }

        $order = Order::where('order_code', $request->query('vnp_TxnRef'))->firstOrFail();
        $success = $request->query('vnp_ResponseCode') === '00'
            && $request->query('vnp_TransactionStatus') === '00';

        return redirect()->route('orders.show', $order)->with(
            $success ? 'success' : 'error',
            $success ? 'VNPay đã nhận thanh toán. Đang chờ IPN xác nhận.' : 'Thanh toán VNPay chưa thành công.'
        );
    }

    public function vnpayIpn(Request $request, VnPayService $vnpay)
    {
        if (! $vnpay->verifyResponse($request)) {
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']);
        }

        $order = Order::where('order_code', $request->query('vnp_TxnRef'))->first();
        if (! $order) return response()->json(['RspCode' => '01', 'Message' => 'Order not found']);
        if ((int) $request->query('vnp_Amount') !== $order->total_amount * 100) {
            return response()->json(['RspCode' => '04', 'Message' => 'Invalid amount']);
        }

        $payment = $order->payments()->where('method', 'vnpay')->latest()->first();
        if (! $payment) return response()->json(['RspCode' => '01', 'Message' => 'Payment not found']);

        $paid = $request->query('vnp_ResponseCode') === '00'
            && $request->query('vnp_TransactionStatus') === '00';
        $payment->update([
            'status' => $paid ? 'success' : 'failed',
            'gateway_transaction_id' => $request->query('vnp_TransactionNo'),
            'gateway_response' => $request->query(),
            'paid_at' => $paid ? now() : null,
        ]);
        $order->update([
            'payment_status' => $paid ? 'paid' : 'failed',
            'order_status' => $paid && $order->order_status === 'pending' ? 'confirmed' : $order->order_status,
        ]);

        return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
    }

    /**
     * GET /thanh-toan/momo/ket-qua — MoMo chuyển hướng TRÌNH DUYỆT của khách về đây
     * sau khi thanh toán xong (hoặc hủy). KHÔNG tin thẳng resultCode trên URL vì
     * đây là dữ liệu đi qua trình duyệt khách, về lý thuyết có thể bị chỉnh sửa —
     * phải tự hỏi lại MoMo (checkStatus) để có câu trả lời đáng tin cậy.
     */
    public function momoReturn(Request $request, MoMoService $momoService)
    {
        $extraData = json_decode(base64_decode((string) $request->query('extraData', '')), true);
        $order = Order::find($extraData['order_id'] ?? null);

        if (! $order) {
            abort(404, 'Không tìm thấy đơn hàng tương ứng.');
        }

        $payment = $order->payments()->latest()->first();

        if (! $payment || ! $payment->gateway_transaction_id) {
            return redirect()->route('orders.show', $order)->with('error', 'Không tìm thấy thông tin giao dịch.');
        }

        $status = $momoService->checkStatus($payment->gateway_transaction_id);

        if (($status['resultCode'] ?? -1) === 0) {
            $order->update([
                'payment_status' => 'paid',
                'order_status' => $order->order_status === 'pending' ? 'confirmed' : $order->order_status,
            ]);

            $payment->update([
                'status' => 'success',
                'paid_at' => now(),
                'gateway_response' => $status,
            ]);

            return redirect()->route('orders.show', $order)->with('success', 'Thanh toán MoMo thành công!');
        }

        return redirect()->route('orders.show', $order)->with('error', 'Thanh toán MoMo không thành công hoặc đã bị hủy.');
    }

    /**
     * POST /thanh-toan/momo/thong-bao — MoMo (SERVER của MoMo, không phải trình
     * duyệt khách) tự động gọi vào đây khi có kết quả thanh toán — đây gọi là "IPN"
     * (Instant Payment Notification). Route này KHÔNG có middleware 'auth' (MoMo
     * không đăng nhập được), và phải được LOẠI TRỪ khỏi CSRF (xem hướng dẫn) vì
     * MoMo không gửi kèm CSRF token của Laravel.
     *
     * LƯU Ý: khi chạy trên localhost, MoMo không thể gọi vào route này được (vì
     * localhost không có địa chỉ công khai trên Internet) — route này vẫn viết
     * đầy đủ, đúng chuẩn để dùng khi deploy thật, còn lúc test trên localhost thì
     * `momoReturn()` ở trên là nơi thực sự xác nhận trạng thái thanh toán.
     */
    public function momoNotify(Request $request, MoMoService $momoService)
    {
        $data = $request->all();

        if (! $momoService->verifyIpnSignature($data)) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $extraData = json_decode(base64_decode($data['extraData'] ?? ''), true);
        $order = Order::find($extraData['order_id'] ?? null);

        if ($order && (int) ($data['resultCode'] ?? -1) === 0) {
            $order->update([
                'payment_status' => 'paid',
                'order_status' => $order->order_status === 'pending' ? 'confirmed' : $order->order_status,
            ]);

            $order->payments()->latest()->first()?->update([
                'status' => 'success',
                'paid_at' => now(),
                'gateway_response' => $data,
            ]);
        }

        // MoMo yêu cầu phản hồi lại (200/204) để biết đã nhận được IPN, nếu không sẽ gọi lại nhiều lần
        return response()->json(['message' => 'ok'], 204);
    }
}
