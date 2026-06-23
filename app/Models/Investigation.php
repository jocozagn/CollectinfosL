<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Investigation extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_PENDING = 'pending';

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'summary',
        'country',
        'theme',
        'places',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(CollaborationRequest::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(InvestigationParticipant::class);
    }

    public function participantUsers()
    {
        return $this->belongsToMany(User::class, 'investigation_participants')
            ->withPivot(['collaboration_request_id', 'joined_at'])
            ->withTimestamps();
    }

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_OPEN => 'Ouverte',
            self::STATUS_CLOSED => 'Fermée',
            self::STATUS_PENDING => 'En validation',
            default => $this->status,
        };
    }

    public function themeLabel(): ?string
    {
        return Content::themeLabels()[$this->theme] ?? $this->theme;
    }

    public static function generateSlug(string $title): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $count = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $original.'-'.$count++;
        }

        return $slug;
    }
}
