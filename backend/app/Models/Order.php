<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    public const ORDER_STATUS_PENDING = 'PENDING';
    public const ORDER_STATUS_PAYMENT_PENDING = 'PAYMENT_PENDING';
    public const ORDER_STATUS_PAID = 'PAID';
    public const ORDER_STATUS_PROCESSING = 'PROCESSING';
    public const ORDER_STATUS_READY = 'READY';
    public const ORDER_STATUS_OUT_FOR_DELIVERY = 'OUT_FOR_DELIVERY';
    public const ORDER_STATUS_DELIVERED = 'DELIVERED';
    public const ORDER_STATUS_CANCELLED = 'CANCELLED';

    public const PAYMENT_STATUS_PENDING = 'PENDING';
    public const PAYMENT_STATUS_SUCCESSFUL = 'SUCCESSFUL';
    public const PAYMENT_STATUS_FAILED = 'FAILED';
    public const PAYMENT_STATUS_CANCELLED = 'CANCELLED';
    public const PAYMENT_STATUS_REFUNDED = 'REFUNDED';

    protected $fillable = [
        'user_id',
        'order_number',
        'subtotal',
        'delivery_fee',
        'total',
        'payment_status',
        'order_status',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function deliveryInformation(): HasOne
    {
        return $this->hasOne(DeliveryInformation::class);
    }

    public static function orderStatusTransitionMap(): array
    {
        return [
            self::ORDER_STATUS_PENDING => [self::ORDER_STATUS_PAYMENT_PENDING, self::ORDER_STATUS_CANCELLED],
            self::ORDER_STATUS_PAYMENT_PENDING => [self::ORDER_STATUS_PAID, self::ORDER_STATUS_CANCELLED],
            self::ORDER_STATUS_PAID => [self::ORDER_STATUS_PROCESSING, self::ORDER_STATUS_CANCELLED],
            self::ORDER_STATUS_PROCESSING => [self::ORDER_STATUS_READY, self::ORDER_STATUS_CANCELLED],
            self::ORDER_STATUS_READY => [self::ORDER_STATUS_OUT_FOR_DELIVERY],
            self::ORDER_STATUS_OUT_FOR_DELIVERY => [self::ORDER_STATUS_DELIVERED],
            self::ORDER_STATUS_DELIVERED => [],
            self::ORDER_STATUS_CANCELLED => [],
        ];
    }

    public function canTransitTo(string $target): bool
    {
        return in_array($target, self::orderStatusTransitionMap()[$this->order_status] ?? [], true);
    }
}
