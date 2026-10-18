<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckoutPayment extends Model
{
    protected $fillable = [
        'user_id',
        'plan',
        'billing_period',
        'amount',
        'currency',
        'status',
        'paypal_order_id',
        'paypal_capture_id',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
