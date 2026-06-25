<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentPurchase extends Model
{
    protected $fillable = [
        'user_id',
        'content_id',
        'price',
        'purchased_at',
        'payment_method',
        'payment_status',
        'payment_reference',
        'invoice_number',
        'platform_fee',
        'journalist_earning',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'purchased_at' => 'datetime',
            'platform_fee' => 'decimal:2',
            'journalist_earning' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }
}
