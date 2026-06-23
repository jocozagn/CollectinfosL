<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isJournalist(): bool
    {
        return $this->role === 'journalist';
    }

    public function isJournalistOrAdmin(): bool
    {
        return in_array($this->role, ['journalist', 'admin'], true);
    }

    public function ownedInvestigations(): HasMany
    {
        return $this->hasMany(Investigation::class, 'user_id');
    }

    public function collaborationRequests(): HasMany
    {
        return $this->hasMany(CollaborationRequest::class);
    }

    public function investigationParticipations(): HasMany
    {
        return $this->hasMany(InvestigationParticipant::class);
    }

    public function participatingInvestigations(): BelongsToMany
    {
        return $this->belongsToMany(Investigation::class, 'investigation_participants')
            ->withPivot(['collaboration_request_id', 'joined_at'])
            ->withTimestamps()
            ->orderByDesc('investigation_participants.joined_at');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(ContentPurchase::class);
    }

    public function purchasedContents(): BelongsToMany
    {
        return $this->belongsToMany(Content::class, 'content_purchases')
            ->withPivot(['price', 'purchased_at'])
            ->withTimestamps();
    }

    public function hasPurchased(Content $content): bool
    {
        return $this->purchases()->where('content_id', $content->id)->exists();
    }

    public function contents()
    {
        return $this->hasMany(Content::class);
    }
}
