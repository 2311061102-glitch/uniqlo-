<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'order_code', 'voucher_id',
        'recipient_name', 'recipient_phone', 'province', 'district', 'ward', 'address_detail',
        'fulfillment_branch_code', 'fulfillment_branch_name', 'fulfillment_branch_address',
        'subtotal_amount', 'shipping_fee', 'discount_amount', 'total_amount',
        'payment_method', 'payment_status', 'order_status', 'qr_expires_at', 'note',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_amount' => 'decimal:2',
            'shipping_fee' => 'integer',
            'discount_amount' => 'integer',
            'total_amount' => 'integer',
            'qr_expires_at' => 'datetime',
        ];
    }

    protected function phone(): Attribute
    {
        return Attribute::make(get: fn () => $this->recipient_phone);
    }

    protected function subtotal(): Attribute
    {
        return Attribute::make(get: fn () => $this->subtotal_amount);
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

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function scopePending($query)
    {
        return $query->where('order_status', 'pending');
    }
}
