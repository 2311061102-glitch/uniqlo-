<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Voucher extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_order_amount', 'max_discount_amount',
        'usage_limit', 'used_count', 'start_date', 'end_date', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
            'is_active'  => 'boolean',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function checkValidity(float $subtotal): array
    {
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'Mã giảm giá không còn hiệu lực.'];
        }

        $today = Carbon::today();

        if ($this->start_date && $today->lt($this->start_date)) {
            return ['valid' => false, 'message' => 'Mã giảm giá chưa đến ngày sử dụng.'];
        }

        if ($this->end_date && $today->gt($this->end_date)) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết hạn.'];
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng.'];
        }

        if ($subtotal < $this->min_order_amount) {
            return [
                'valid' => false,
                'message' => 'Đơn hàng cần tối thiểu ' . number_format($this->min_order_amount) . 'đ để dùng mã này.',
            ];
        }

        return ['valid' => true, 'message' => ''];
    }

    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === 'percent') {
            $discount = $subtotal * ($this->value / 100);
            if ($this->max_discount_amount !== null) {
                $discount = min($discount, $this->max_discount_amount);
            }
        } else {
            $discount = $this->value;
        }

        return min($discount, $subtotal);
    }
}