<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'method', 'amount', 'status',
        'gateway_transaction_id', 'gateway_response', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'gateway_response' => 'array', // Laravel tự động encode/decode JSON <-> mảng PHP
            'paid_at' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
