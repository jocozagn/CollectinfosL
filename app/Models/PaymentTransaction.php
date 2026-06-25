<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_REDIRECTED = 'redirected';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    public const TYPE_CART = 'cart';

    public const TYPE_SUBSCRIPTION = 'subscription';

    protected $fillable = [
        'user_id',
        'type',
        'reference',
        'payment_method',
        'amount',
        'currency',
        'djomy_amount',
        'status',
        'djomy_transaction_id',
        'redirect_url',
        'cart_snapshot',
        'metadata',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'djomy_amount' => 'integer',
            'cart_snapshot' => 'array',
            'metadata' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contentIds(): array
    {
        return $this->cart_snapshot['content_ids'] ?? [];
    }

    public function subscriptionProductId(): ?int
    {
        $id = $this->cart_snapshot['site_product_id'] ?? null;

        return $id ? (int) $id : null;
    }

    public function isSubscription(): bool
    {
        return $this->type === self::TYPE_SUBSCRIPTION;
    }

    public function isPending(): bool
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_REDIRECTED], true);
    }
}
