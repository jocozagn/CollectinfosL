<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Taxonomy extends Model
{
    use HasTranslations;

    public const KIND_CATEGORY = 'category';

    public const KIND_THEME = 'theme';

    public const KIND_TYPE = 'type';

    protected $fillable = [
        'kind',
        'name',
        'slug',
        'image',
        'icon',
        'description',
        'sort_order',
        'is_active',
        'show_on_home',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_on_home' => 'boolean',
        ];
    }

    public static function kindLabels(): array
    {
        return [
            self::KIND_CATEGORY => 'Catégories',
            self::KIND_THEME => 'Thèmes',
            self::KIND_TYPE => 'Types de contenu',
        ];
    }

    public static function routeKey(string $kind): string
    {
        return match ($kind) {
            self::KIND_CATEGORY => 'categories',
            self::KIND_THEME => 'themes',
            self::KIND_TYPE => 'types',
            default => $kind,
        };
    }

    public static function kindFromRoute(string $routeKey): ?string
    {
        return match ($routeKey) {
            'categories' => self::KIND_CATEGORY,
            'themes' => self::KIND_THEME,
            'types' => self::KIND_TYPE,
            default => null,
        };
    }

    public static function labels(string $kind): array
    {
        try {
            $labels = static::query()
                ->where('kind', $kind)
                ->where('is_active', true)
                ->with('translations')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->mapWithKeys(fn (self $item) => [$item->slug => $item->trans('name')])
                ->all();

            if ($labels !== []) {
                return $labels;
            }
        } catch (\Throwable) {
            // Base non migrée
        }

        return static::defaultLabels($kind);
    }

    public static function homeCategories(): array
    {
        try {
            $items = static::query()
                ->where('kind', self::KIND_CATEGORY)
                ->where('is_active', true)
                ->where('show_on_home', true)
                ->with('translations')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            if ($items->isNotEmpty()) {
                return $items->map(fn (self $item) => [
                    'name' => strtoupper($item->trans('name')),
                    'url' => route('contents.index', ['categorie' => $item->slug]),
                    'image' => $item->imageUrl() ?? 'https://via.placeholder.com/267x300?text='.urlencode($item->name),
                ])->all();
            }
        } catch (\Throwable) {
            //
        }

        return config('collectinfos.categories', []);
    }

    public static function defaultLabels(string $kind): array
    {
        return match ($kind) {
            self::KIND_TYPE => [
                'video' => 'Vidéo',
                'article' => 'Article',
                'audio' => 'Audio',
                'photo' => 'Photo',
                'infographic' => 'Infographie',
            ],
            self::KIND_THEME => [
                'politique' => 'Politique',
                'economie' => 'Économie',
                'societe' => 'Société',
                'securite' => 'Sécurité',
                'culture' => 'Culture',
                'environnement' => 'Environnement',
            ],
            self::KIND_CATEGORY => [
                'experts' => 'Experts',
                'medias' => 'Médias',
                'organisations' => 'Organisations',
                'particuliers' => 'Particuliers',
            ],
            default => [],
        };
    }

    public static function generateSlug(string $name, string $kind, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $count = 1;

        while (static::query()
            ->where('kind', $kind)
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $original.'-'.$count++;
        }

        return $slug;
    }

    public function imageUrl(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http')) {
            return $this->image;
        }

        return asset('storage/'.$this->image);
    }

    public function kindLabel(): string
    {
        return self::kindLabels()[$this->kind] ?? $this->kind;
    }

    public function isInUse(): bool
    {
        return match ($this->kind) {
            self::KIND_CATEGORY => Content::where('category', $this->slug)->exists(),
            self::KIND_THEME => Content::where('theme', $this->slug)->exists()
                || Investigation::where('theme', $this->slug)->exists(),
            self::KIND_TYPE => Content::where('type', $this->slug)->exists(),
            default => false,
        };
    }
}
