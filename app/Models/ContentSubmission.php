<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentSubmission extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_REVIEW = 'in_review';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'user_id',
        'content_id',
        'reviewed_by',
        'title',
        'summary',
        'type',
        'theme',
        'category',
        'country',
        'region',
        'city',
        'keywords',
        'access',
        'price',
        'rights',
        'negotiable',
        'thumbnail',
        'media_path',
        'file_format',
        'resolution',
        'duration',
        'file_size',
        'content_date',
        'exclusivity_expires_at',
        'gps_lat',
        'gps_lng',
        'status',
        'review_note',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'negotiable' => 'boolean',
            'content_date' => 'date',
            'exclusivity_expires_at' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'En attente',
            self::STATUS_IN_REVIEW => 'En modération',
            self::STATUS_APPROVED => 'Validé',
            self::STATUS_REJECTED => 'Rejeté',
            self::STATUS_PUBLISHED => 'Publié',
        ];
    }

    public function statusLabel(): string
    {
        return static::statusLabels()[$this->status] ?? $this->status;
    }

    public function thumbnailUrl(): ?string
    {
        return $this->resolveAssetUrl($this->thumbnail);
    }

    private function resolveAssetUrl(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        return asset('storage/'.$path);
    }
}
