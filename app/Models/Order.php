<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUSES = [
        'pending', 'paid', 'processing', 'shipped', 'delivered', 'cancelled',
    ];

    protected $fillable = [
        'user_id', 'order_number', 'status', 'subtotal', 'shipping', 'total',
        'currency', 'payment_gateway', 'payment_reference', 'payment_status',
        'shipping_address', 'billing_address',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'billing_address'  => 'array',
        'subtotal'         => 'decimal:2',
        'shipping'         => 'decimal:2',
        'total'            => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }
}
