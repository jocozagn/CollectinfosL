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
