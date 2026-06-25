<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSubscription extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'site_product_id',
        'status',
        'starts_at',
        'ends_at',
        'payment_method',
        'payment_reference',
        'price_eur',
        'price_gnf',
        'payment_transaction_id',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'price_eur' => 'decimal:2',
            'price_gnf' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(SiteProduct::class, 'site_product_id');
    }

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && $this->ends_at->isFuture();
    }

    public function billingLabel(): string
    {
        $months = (int) ($this->product?->billing_months ?? 1);

        return match ($months) {
            1 => 'Mensuel',
            12 => 'Annuel',
            default => $months.' mois',
        };
    }
}
