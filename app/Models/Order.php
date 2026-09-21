<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_code', 'user_id', 'voucher_id', 'address_id',
        'recipient_name', 'recipient_phone', 'province', 'district', 'ward', 'address_detail',
        'subtotal_amount', 'shipping_fee', 'discount_amount', 'total_amount',
        'payment_method', 'payment_status', 'order_status',
        'qr_expires_at',
    ];

    const STATUSES = ['pending', 'processing', 'shipping', 'completed', 'cancelled'];
    const CANCELLABLE_STATUSES = ['pending'];

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

    public function canBeCancelled(): bool
    {
        return in_array($this->order_status, self::CANCELLABLE_STATUSES);
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
    
    protected function casts(): array
    {
        return [
            'qr_expires_at' => 'datetime',
        ];
    }
}