<?php

namespace App\Http\Controllers;

use App\Models\Order;
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
}
