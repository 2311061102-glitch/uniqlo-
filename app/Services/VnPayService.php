<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use RuntimeException;

class VnPayService
{
    public function createPaymentUrl(Order $order, Request $request): string
    {
        $tmnCode = trim((string) config('services.vnpay.tmn_code'));
        $hashSecret = trim((string) config('services.vnpay.hash_secret'));
        $paymentUrl = (string) config('services.vnpay.payment_url');
        $returnUrl = (string) config('services.vnpay.return_url');

        if ($tmnCode === '' || $hashSecret === '' || $paymentUrl === '' || $returnUrl === '') {
            throw new RuntimeException('VNPay Sandbox chưa được cấu hình đầy đủ.');
        }

        $now = now('Asia/Ho_Chi_Minh');
        $input = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $tmnCode,
            'vnp_Amount' => $order->total_amount * 100,
            'vnp_CreateDate' => $now->format('YmdHis'),
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => $request->ip(),
            'vnp_Locale' => 'vn',
            'vnp_OrderInfo' => 'Thanh toan don hang '.$order->order_code,
            'vnp_OrderType' => 'other',
            'vnp_ReturnUrl' => $returnUrl,
            'vnp_TxnRef' => $order->order_code,
            'vnp_ExpireDate' => $now->copy()->addMinutes(15)->format('YmdHis'),
        ];

        ksort($input);
        $query = $this->encode($input);
        $hash = hash_hmac('sha512', $query, $hashSecret);

        return $paymentUrl.'?'.$query.'&vnp_SecureHash='.$hash;
    }

    public function verifyResponse(Request $request): bool
    {
        $received = strtolower((string) $request->query('vnp_SecureHash'));
        if ($received === '') {
            return false;
        }

        $input = collect($request->query())
            ->filter(fn ($value, $key) => str_starts_with($key, 'vnp_'))
            ->except(['vnp_SecureHash', 'vnp_SecureHashType'])
            ->sortKeys()
            ->all();

        $query = $this->encode($input);
        $calculated = hash_hmac('sha512', $query, trim((string) config('services.vnpay.hash_secret')));

        return hash_equals($calculated, $received);
    }

    private function encode(array $input): string
    {
        return collect($input)
            ->map(fn ($value, $key) => urlencode($key).'='.urlencode((string) $value))
            ->implode('&');
    }
}
