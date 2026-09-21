<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SepayWebhookController extends Controller
{
    /**
     * Endpoint thật mà SePay sẽ gọi (cấu hình URL này trên dashboard SePay).
     * SePay tính là THÀNH CÔNG khi ta trả về đúng: HTTP 200/201 + JSON {"success": true}
     * trong vòng 30 giây — trả sai 1 trong 2 điều kiện, SePay sẽ tự động gọi lại (retry).
     */
    public function handle(Request $request)
    {
        if (! $this->isAuthenticSepayRequest($request)) {
            Log::warning('Webhook SePay bị từ chối: sai API Key.');
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $this->processPayload($request->all());

        // Luôn trả success:true cho các trường hợp "không liên quan" (tiền ra, không
        // tìm thấy mã đơn...) để SePay không hiểu nhầm là lỗi rồi cứ gọi lại (retry) mãi.
        return response()->json(['success' => true]);
    }

    /**
     * Route CHỈ DÙNG ĐỂ DEMO/TEST cục bộ khi CHƯA có tài khoản ngân hàng thật kết nối SePay.
     * Giả lập ĐÚNG payload SePay sẽ gửi, chạy qua chung 1 hàm xử lý processPayload()
     * với webhook thật -> đảm bảo logic test giống hệt logic chạy thật.
     */
    public function simulate(Request $request, Order $order)
    {
        abort_unless(app()->environment('local'), 404); // chỉ hoạt động ở môi trường local
        abort_unless($order->user_id === auth()->id(), 403);

        $fakePayload = [
            'id'              => random_int(100000, 999999),
            'gateway'         => 'SePay Demo',
            'transactionDate' => now()->format('Y-m-d H:i:s'),
            'accountNumber'   => config('services.vietqr.account_no'),
            'code'            => $order->order_code,
            'content'         => $order->order_code . ' thanh toan don hang',
            'transferType'    => 'in',
            'transferAmount'  => (int) $order->total_amount,
            'referenceCode'   => 'DEMO' . now()->format('YmdHis'),
        ];

        $this->processPayload($fakePayload);

        return redirect()->route('checkout.success', $order)
            ->with('success', '[DEMO] Đã giả lập giao dịch chuyển khoản thành công.');
    }

    /**
     * Logic xử lý dùng chung cho cả webhook thật và route giả lập test.
     */
    private function processPayload(array $payload): void
    {
        // Chỉ xử lý giao dịch TIỀN VÀO, bỏ qua tiền ra
        if (($payload['transferType'] ?? null) !== 'in') {
            return;
        }

        // Chống xử lý trùng 1 giao dịch nhiều lần (SePay có thể gửi lại webhook do retry)
        $gatewayId = (string) ($payload['id'] ?? '');
        if ($gatewayId && \App\Models\Payment::where('gateway_transaction_id', $gatewayId)->exists()) {
            return;
        }

        // Tìm mã đơn hàng (dạng ORD + số) nằm trong nội dung chuyển khoản
        $content = $payload['content'] ?? '';
        if (! preg_match('/ORD\d+/', $content, $matches)) {
            return;
        }

        $order = Order::where('order_code', $matches[0])->first();
        if (! $order || ! $order->payment) {
            return;
        }

        // Đơn đã thanh toán rồi thì không xử lý lại
        if ($order->payment_status === 'paid') {
            return;
        }

        // BẢO MẬT QUAN TRỌNG: số tiền chuyển khoản phải khớp đúng với đơn hàng,
        // tránh trường hợp khách chuyển thiếu tiền nhưng hệ thống vẫn tự xác nhận "đã thanh toán".
        $transferAmount = (int) ($payload['transferAmount'] ?? 0);
        if ($transferAmount !== (int) $order->total_amount) {
            Log::warning("Webhook SePay: số tiền không khớp cho đơn {$order->order_code}. Nhận {$transferAmount}, cần {$order->total_amount}.");
            return;
        }

        DB::transaction(function () use ($order, $payload, $gatewayId) {
            $order->payment()->update([
                'status'                 => 'success',
                'transaction_code'       => $payload['referenceCode'] ?? null,
                'gateway_transaction_id' => $gatewayId ?: null,
                'raw_webhook_payload'    => json_encode($payload),
                'paid_at'                => now(),
            ]);

            $order->update([
                'payment_status' => 'paid',
            ]);
        });
    }

    /**
     * Xác thực request thật sự đến từ SePay bằng API Key,
     * theo đúng phương thức "API Key" mà SePay hỗ trợ:
     * header Authorization: Apikey <API_KEY_CUA_BAN>
     */
    private function isAuthenticSepayRequest(Request $request): bool
    {
        $expected = config('services.sepay.webhook_api_key');

        if (! $expected) {
            return false;
        }

        $authHeader = $request->header('Authorization', '');

        if (! str_starts_with($authHeader, 'Apikey ')) {
            return false;
        }

        $provided = substr($authHeader, 7);

        return hash_equals($expected, $provided);
    }
}