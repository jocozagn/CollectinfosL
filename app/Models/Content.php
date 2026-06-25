<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Services\CurrencyService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Content extends Model
{
    use HasTranslations;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'summary',
        'body',
        'type',
        'theme',
        'country',
        'category',
        'access',
        'price',
        'price_gnf',
        'duration',
        'thumbnail',
        'media_path',
        'preview_enabled',
        'preview_seconds',
        'preview_excerpt',
        'preview_media_path',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'price_gnf' => 'integer',
            'published_at' => 'datetime',
            'preview_enabled' => 'boolean',
            'preview_seconds' => 'integer',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if (request()->is('admin/*')) {
            return parent::resolveRouteBinding($value, $field);
        }

        return static::published()->where($field ?? $this->getRouteKeyName(), $value)->firstOrFail();
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(ContentPurchase::class);
    }

    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'content_favorites')
            ->withTimestamps();
    }

    public function userHasAccess(?User $user = null): bool
    {
        if ($this->isFree()) {
            return true;
        }

        if ($this->access === 'subscriber') {
            return $user !== null && $user->hasActiveSubscription();
        }

        if ($this->isPaid() || $this->isExclusive()) {
            return $user !== null && $user->hasPurchased($this);
        }

        return true;
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isFree(): bool
    {
        return $this->access === 'free' && (is_null($this->price) || (float) $this->price <= 0);
    }

    public function isPaid(): bool
    {
        return ! is_null($this->price) && (float) $this->price > 0;
    }

    public function isExclusive(): bool
    {
        return $this->access === 'exclusive';
    }

    public function isSoldExclusively(): bool
    {
        if (! $this->isExclusive()) {
            return false;
        }

        if (isset($this->purchases_count)) {
            return $this->purchases_count > 0;
        }

        return $this->purchases()->exists();
    }

    public function exclusiveBuyer(): ?User
    {
        return $this->purchases()->with('user')->oldest('purchased_at')->first()?->user;
    }

    public function isPurchasable(): bool
    {
        if (! $this->isPublished() || ! $this->isPaid()) {
            return false;
        }

        if ($this->access === 'subscriber') {
            return false;
        }

        if ($this->isExclusive() && $this->isSoldExclusively()) {
            return false;
        }

        return true;
    }

    public function isSubscriberOnly(): bool
    {
        return $this->access === 'subscriber';
    }

    public static function accessLabels(): array
    {
        return [
            'free' => 'Libre',
            'subscriber' => 'Abonnés',
            'exclusive' => 'Exclusif (1 acheteur)',
        ];
    }

    public function accessLabel(): string
    {
        return static::accessLabels()[$this->access] ?? $this->access;
    }

    public function thumbnailUrl(): ?string
    {
        return $this->resolveAssetUrl($this->thumbnail);
    }

    public function mediaUrl(): ?string
    {
        return $this->resolveAssetUrl($this->media_path);
    }

    public function previewMediaUrl(): ?string
    {
        $path = $this->preview_media_path ?: $this->media_path;

        return $this->resolveAssetUrl($path);
    }

    public function previewText(): ?string
    {
        if ($this->preview_excerpt) {
            return $this->trans('preview_excerpt');
        }

        if ($this->summary) {
            return $this->trans('summary');
        }

        if ($this->body) {
            return Str::limit(strip_tags($this->trans('body')), 400);
        }

        return null;
    }

    public function hasPreview(): bool
    {
        if (! $this->preview_enabled) {
            return false;
        }

        if ($this->previewMediaUrl() || $this->youtubeEmbedId()) {
            return true;
        }

        return $this->previewText() !== null;
    }

    public function youtubeEmbedId(?string $url = null): ?string
    {
        $url = $url ?? ($this->preview_media_path ?: $this->media_path);

        if (! $url) {
            return null;
        }

        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function previewEmbedUrl(): ?string
    {
        $id = $this->youtubeEmbedId();

        if (! $id) {
            return null;
        }

        $seconds = max(5, min(120, (int) ($this->preview_seconds ?: 15)));

        return "https://www.youtube.com/embed/{$id}?autoplay=1&mute=1&start=0&end={$seconds}&controls=0&modestbranding=1&rel=0";
    }

    public function previewMode(): string
    {
        if ($this->youtubeEmbedId()) {
            return 'youtube';
        }

        $url = $this->previewMediaUrl();

        if ($url && in_array($this->type, ['video'], true) && preg_match('/\.(mp4|webm|ogg)(\?|$)/i', $url)) {
            return 'video';
        }

        if ($url && in_array($this->type, ['audio'], true) && preg_match('/\.(mp3|wav|ogg|m4a)(\?|$)/i', $url)) {
            return 'audio';
        }

        return 'text';
    }

    public function toCardArray(): array
    {
        $typeLabels = static::typeLabels();

        return [
            'title' => $this->trans('title'),
            'slug' => $this->slug,
            'image' => $this->thumbnailUrl() ?? 'https://via.placeholder.com/300x300?text=Collectinfos',
            'price' => $this->price,
            'action' => $this->isPurchasable() ? 'cart' : 'read',
            'type' => $this->type,
            'type_label' => $typeLabels[$this->type] ?? ucfirst($this->type),
            'access' => $this->access,
            'is_free' => $this->isFree(),
            'is_paid' => $this->isPaid(),
            'is_exclusive' => $this->isExclusive(),
            'is_exclusive_sold' => $this->isSoldExclusively(),
            'is_purchasable' => $this->isPurchasable(),
            'preview_enabled' => $this->hasPreview(),
            'preview_seconds' => max(5, min(120, (int) ($this->preview_seconds ?: 15))),
            'preview_mode' => $this->previewMode(),
            'preview_text' => $this->previewText(),
            'preview_url' => $this->previewMediaUrl(),
            'preview_embed' => $this->previewEmbedUrl(),
        ];
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

    public function localMediaPath(): ?string
    {
        if (! $this->media_path || str_starts_with($this->media_path, 'http')) {
            return null;
        }

        return $this->media_path;
    }

    public function hasDownloadableFile(): bool
    {
        $path = $this->localMediaPath();

        return $path !== null && \Illuminate\Support\Facades\Storage::disk('public')->exists($path);
    }

    public function downloadFilename(): string
    {
        $extension = pathinfo((string) $this->media_path, PATHINFO_EXTENSION);
        $base = Str::slug($this->title) ?: 'contenu-collectinfos';

        return $extension ? $base.'.'.$extension : $base;
    }

    public function deliveryMediaUrl(?User $user = null): ?string
    {
        if (! $this->userHasAccess($user)) {
            return null;
        }

        if ($this->youtubeEmbedId($this->media_path)) {
            return $this->mediaUrl();
        }

        if ($this->hasDownloadableFile()) {
            return route('contents.media', $this->slug);
        }

        return $this->mediaUrl();
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

    public static function typeLabels(): array
    {
        return Taxonomy::labels(Taxonomy::KIND_TYPE);
    }

    public static function themeLabels(): array
    {
        return Taxonomy::labels(Taxonomy::KIND_THEME);
    }

    public static function categoryLabels(): array
    {
        return Taxonomy::labels(Taxonomy::KIND_CATEGORY);
    }
}
