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
        'subtotal_amount', 'shipping_fee', 'discount_amount', 'total_amount',
        'payment_method', 'payment_status', 'order_status', 'note',
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

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function scopePending($query)
    {
        return $query->where('order_status', 'pending');
    }

    /**
     * Ghép địa chỉ đầy đủ thành 1 dòng để hiển thị, VD:
     * "12 Nguyễn Trãi, Phường 5, Quận 1, TP.HCM"
     */
    public function fullAddress(): string
    {
        return implode(', ', array_filter([
            $this->address_detail, $this->ward, $this->district, $this->province,
        ]));
    }
}
