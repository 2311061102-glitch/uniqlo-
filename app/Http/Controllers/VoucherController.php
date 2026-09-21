<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use Carbon\Carbon;

class VoucherController extends Controller
{
    /**
     * Hiển thị danh sách voucher đang có hiệu lực để khách hàng xem/lấy mã.
     */
    public function index()
    {
        $today = Carbon::today();

        $vouchers = Voucher::where('is_active', true)
            ->where(function ($query) use ($today) {
                $query->whereNull('start_date')->orWhereDate('start_date', '<=', $today);
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
            })
            ->where(function ($query) {
                $query->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
            })
            ->latest()
            ->get();

        return view('vouchers.index', compact('vouchers'));
    }
}