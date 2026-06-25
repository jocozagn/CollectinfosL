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

#[Fillable(['name', 'email', 'password', 'role', 'phone', 'country', 'city', 'profile_slug', 'bio', 'account_type', 'wallet_balance', 'profile_meta', 'profile_verified_at'])]
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
            'wallet_balance' => 'decimal:2',
            'profile_meta' => 'array',
            'profile_verified_at' => 'datetime',
        ];
    }

    public function meta(string $key, mixed $default = null): mixed
    {
        return data_get($this->profile_meta, $key, $default);
    }

    public function isProfileVerified(): bool
    {
        return $this->profile_verified_at !== null;
    }

    public function isBuyer(): bool
    {
        return $this->role === 'user';
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
            ->withPivot(['collaboration_request_id', 'role', 'joined_at'])
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

    public function favoriteContents(): BelongsToMany
    {
        return $this->belongsToMany(Content::class, 'content_favorites')
            ->withTimestamps()
            ->orderByDesc('content_favorites.created_at');
    }

    public function hasFavorited(Content $content): bool
    {
        return $this->favoriteContents()->where('content_id', $content->id)->exists();
    }

    public function hasPurchased(Content $content): bool
    {
        return $this->purchases()->where('content_id', $content->id)->exists();
    }

    public function contents()
    {
        return $this->hasMany(Content::class);
    }

    public function contentSubmissions(): HasMany
    {
        return $this->hasMany(ContentSubmission::class);
    }

    public function contentOrders(): HasMany
    {
        return $this->hasMany(ContentOrder::class);
    }

    public function assignedOrders(): HasMany
    {
        return $this->hasMany(ContentOrder::class, 'assigned_journalist_id');
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function activeSubscription(): ?UserSubscription
    {
        return $this->subscriptions()
            ->where('status', UserSubscription::STATUS_ACTIVE)
            ->where('ends_at', '>', now())
            ->latest('ends_at')
            ->first();
    }

    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    public function subscriptionDiscountPercent(): int
    {
        $subscription = $this->activeSubscription();

        if (! $subscription || ! $subscription->product) {
            return 0;
        }

        return max(0, min(100, (int) $subscription->product->discount_percent));
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }

    public function publicProfileUrl(): ?string
    {
        if (! $this->profile_slug || ! $this->isJournalist()) {
            return null;
        }

        return route('journalists.show', $this->profile_slug);
    }

    public function accountTypeLabel(): string
    {
        $labels = config('collectinfos.account_types', []);

        return $labels[$this->account_type] ?? ($this->isJournalist() ? 'Journaliste' : 'Acheteur');
    }
}
