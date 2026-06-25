<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestigationDraft extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING_REVIEW = 'pending_review';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'investigation_id',
        'user_id',
        'title',
        'body',
        'status',
        'reviewed_by',
        'review_note',
        'reviewed_at',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function investigation(): BelongsTo
    {
        return $this->belongsTo(Investigation::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING_REVIEW => 'En validation',
            self::STATUS_APPROVED => 'Validé',
            self::STATUS_REJECTED => 'À retravailler',
            default => 'Brouillon',
        };
    }

    public function isEditableBy(User $user): bool
    {
        return $this->user_id === $user->id
            && in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED], true);
    }
}
