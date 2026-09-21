<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Gói gọn toàn bộ logic gọi API MoMo vào 1 class riêng (Service class) thay vì
 * viết thẳng trong Controller — giúp Controller gọn hơn, và nếu sau này cần
 * dùng lại logic này ở chỗ khác (VD: 1 job tự động đối soát cuối ngày) thì chỉ
 * cần gọi lại class này, không phải copy code.
 */
class MoMoService
{
    private function config(string $key)
    {
        return config("services.momo.$key");
    }

    /**
     * Gọi API MoMo để lấy "payUrl" — link thanh toán, cần chuyển hướng khách sang đó.
     * Trả về null nếu MoMo từ chối yêu cầu (VD sai chữ ký, sai thông tin).
     */
    public function createPaymentUrl(Order $order): ?string
    {
        $partnerCode = $this->config('partner_code');
        $accessKey = $this->config('access_key');
        $secretKey = $this->config('secret_key');
        $endpoint = $this->config('endpoint');

        $requestId = (string) Str::uuid();

        // orderId gửi cho MoMo phải DUY NHẤT TUYỆT ĐỐI — khác với order_code của mình,
        // vì nếu khách thanh toán thất bại rồi thử lại, orderId gửi MoMo lần sau phải khác lần trước.
        $momoOrderId = $order->order_code.'-'.time();
        $orderInfo = 'Thanh toan don hang '.$order->order_code;
        $amount = (string) $order->total_amount;
        $redirectUrl = route('payments.momo.return');
        $ipnUrl = route('payments.momo.notify');
        $requestType = 'captureWallet';
        $extraData = base64_encode(json_encode(['order_id' => $order->id]));

        /*
         * Chuỗi ký (raw signature) PHẢI đúng thứ tự từng field theo tài liệu MoMo quy định —
         * sai thứ tự dù chỉ 1 field cũng khiến chữ ký sai hoàn toàn, MoMo sẽ từ chối request.
         */
        $rawSignature = "accessKey={$accessKey}&amount={$amount}&extraData={$extraData}"
            ."&ipnUrl={$ipnUrl}&orderId={$momoOrderId}&orderInfo={$orderInfo}"
            ."&partnerCode={$partnerCode}&redirectUrl={$redirectUrl}&requestId={$requestId}"
            ."&requestType={$requestType}";

        $signature = hash_hmac('sha256', $rawSignature, $secretKey);

        $response = Http::post($endpoint, [
            'partnerCode' => $partnerCode,
            'accessKey' => $accessKey,
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $momoOrderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature,
            'lang' => 'vi',
        ]);

        $data = $response->json();

        if (($data['resultCode'] ?? -1) !== 0) {
            return null;
        }

        // Lưu momoOrderId vào bản ghi payment để sau này tra cứu đúng giao dịch này ở MoMo
        $order->payments()->latest()->first()?->update([
            'gateway_transaction_id' => $momoOrderId,
        ]);

        return $data['payUrl'] ?? null;
    }

    /**
     * Gọi API "Check Transaction Status" của MoMo — hỏi thẳng MoMo xem giao dịch
     * đã thanh toán thành công chưa.
     *
     * ĐÂY LÀ CÁCH XÁC NHẬN ĐÁNG TIN CẬY NHẤT khi đang chạy trên localhost: IPN
     * (webhook) là MoMo GỌI VÀO máy mình — nhưng máy mình chạy localhost thì MoMo
     * (ở ngoài internet) không thể gọi vào được. Ngược lại, hàm này là máy mình
     * CHỦ ĐỘNG GỌI RA ngoài để hỏi MoMo — chiều này luôn hoạt động bình thường dù
     * đang chạy localhost, không bị giới hạn gì cả.
     */
    public function checkStatus(string $momoOrderId): array
    {
        $partnerCode = $this->config('partner_code');
        $accessKey = $this->config('access_key');
        $secretKey = $this->config('secret_key');

        $requestId = (string) Str::uuid();

        $rawSignature = "accessKey={$accessKey}&orderId={$momoOrderId}&partnerCode={$partnerCode}&requestId={$requestId}";
        $signature = hash_hmac('sha256', $rawSignature, $secretKey);

        $response = Http::post('https://test-payment.momo.vn/v2/gateway/api/query', [
            'partnerCode' => $partnerCode,
            'accessKey' => $accessKey,
            'requestId' => $requestId,
            'orderId' => $momoOrderId,
            'signature' => $signature,
            'lang' => 'vi',
        ]);

        return $response->json() ?? [];
    }

    /**
     * Kiểm tra chữ ký của dữ liệu IPN (webhook) MoMo gửi về — đảm bảo đúng là
     * MoMo gửi thật, không phải ai đó giả mạo request để đánh lừa hệ thống rằng
     * "đã thanh toán" trong khi thực tế khách chưa trả tiền.
     */
    public function verifyIpnSignature(array $data): bool
    {
        $accessKey = $this->config('access_key');
        $secretKey = $this->config('secret_key');

        $rawSignature = "accessKey={$accessKey}"
            ."&amount={$data['amount']}&extraData={$data['extraData']}"
            ."&message={$data['message']}&orderId={$data['orderId']}"
            ."&orderInfo={$data['orderInfo']}&orderType={$data['orderType']}"
            ."&partnerCode={$data['partnerCode']}&payType={$data['payType']}"
            ."&requestId={$data['requestId']}&responseTime={$data['responseTime']}"
            ."&resultCode={$data['resultCode']}&transId={$data['transId']}";

        $expected = hash_hmac('sha256', $rawSignature, $secretKey);

        return hash_equals($expected, $data['signature'] ?? '');
    }
}
