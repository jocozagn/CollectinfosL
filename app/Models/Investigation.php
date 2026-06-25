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

    public function pendingJoinRequests(): HasMany
    {
        return $this->requests()
            ->where('type', 'join')
            ->where('status', 'pending');
    }

    public function participantsCount(): int
    {
        return $this->participants()->count();
    }

    public function remainingPlaces(): int
    {
        return max(0, $this->places - $this->participantsCount());
    }

    public function hasAvailablePlace(): bool
    {
        return $this->remainingPlaces() > 0;
    }

    public function participants(): HasMany
    {
        return $this->hasMany(InvestigationParticipant::class);
    }

    public function participantUsers()
    {
        return $this->belongsToMany(User::class, 'investigation_participants')
            ->withPivot(['collaboration_request_id', 'role', 'joined_at'])
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(InvestigationMessage::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(InvestigationFile::class);
    }

    public function drafts(): HasMany
    {
        return $this->hasMany(InvestigationDraft::class);
    }

    public function isOwner(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    public function isMember(User $user): bool
    {
        if ($this->isOwner($user)) {
            return true;
        }

        return $this->participants()->where('user_id', $user->id)->exists();
    }

    public function participantRecordFor(User $user): ?InvestigationParticipant
    {
        return $this->participants()->where('user_id', $user->id)->first();
    }

    public function memberRole(User $user): ?string
    {
        if ($this->isOwner($user)) {
            return 'owner';
        }

        return $this->participantRecordFor($user)?->role;
    }

    public function canContribute(User $user): bool
    {
        if ($this->isOwner($user)) {
            return true;
        }

        $role = $this->memberRole($user);

        return in_array($role, ['lead', 'contributor'], true);
    }

    public function canManageTeam(User $user): bool
    {
        if ($this->isOwner($user)) {
            return true;
        }

        return $this->memberRole($user) === 'lead';
    }

    public function canReviewDrafts(User $user): bool
    {
        return $this->canManageTeam($user);
    }

    public function roleLabelFor(User $user): string
    {
        return match ($this->memberRole($user)) {
            'owner' => 'Porteur',
            'lead' => 'Coordinateur',
            'contributor' => 'Contributeur',
            'viewer' => 'Lecteur',
            default => 'Membre',
        };
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
