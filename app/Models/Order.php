<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_code',
        'recipient_name', 'phone', 'province', 'district', 'ward', 'address_detail',
        'subtotal', 'shipping_fee', 'discount_amount', 'total_amount',
        'payment_method', 'payment_status', 'order_status', 'note',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'shipping_fee' => 'integer',
            'discount_amount' => 'integer',
            'total_amount' => 'integer',
        ];
    }

    /**
     * Tự sinh mã đơn hàng dạng "DH" + ngày tháng năm + 4 ký tự ngẫu nhiên,
     * VD: DH202608240 7F3A — dễ đọc hơn nhiều so với chỉ hiện ID số (VD "Đơn #4").
     */
    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_code)) {
                $order->order_code = 'DH'.now()->format('Ymd').strtoupper(Str::random(4));
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function scopePending($query)
    {
        return $query->where('order_status', 'pending');
    }
}
