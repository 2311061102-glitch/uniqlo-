<?php

namespace App\Services;

use Illuminate\Support\Str;

class VietQrService
{
    /**
     * Sinh URL ảnh QR VietQR (Quick Link) đã điền sẵn đúng số tiền và nội dung
     * chuyển khoản (chính là mã đơn hàng), theo cú pháp chính thức của VietQR.io:
     * https://img.vietqr.io/image/<BANK_ID>-<ACCOUNT_NO>-<TEMPLATE>.png?amount=...&addInfo=...&accountName=...
     */
    public static function buildImageUrl(string $orderCode, float $amount): string
    {
        $bankId = config('services.vietqr.bank_bin');
        $accountNo = config('services.vietqr.account_no');
        $accountName = config('services.vietqr.account_name');
        $template = 'compact2'; // gồm QR + logo ngân hàng + đầy đủ thông tin chuyển khoản

        // VietQR Quick Link giới hạn addInfo tối đa 25 ký tự, khuyến khích không dấu.
        $addInfo = Str::ascii($orderCode);
        $addInfo = substr($addInfo, 0, 25);

        $query = http_build_query([
            'amount'      => (int) $amount,
            'addInfo'     => $addInfo,
            'accountName' => Str::ascii($accountName),
        ]);

        return "https://img.vietqr.io/image/{$bankId}-{$accountNo}-{$template}.png?{$query}";
    }
}