<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
            'published_at' => 'datetime',
            'preview_enabled' => 'boolean',
            'preview_seconds' => 'integer',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(ContentPurchase::class);
    }

    public function userHasAccess(?User $user = null): bool
    {
        if ($this->isFree()) {
            return true;
        }

        if ($this->isPaid()) {
            return $user !== null && $user->hasPurchased($this);
        }

        return $this->access !== 'subscriber';
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
            'action' => $this->isPaid() ? 'cart' : 'read',
            'type' => $this->type,
            'type_label' => $typeLabels[$this->type] ?? ucfirst($this->type),
            'access' => $this->access,
            'is_free' => $this->isFree(),
            'is_paid' => $this->isPaid(),
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
