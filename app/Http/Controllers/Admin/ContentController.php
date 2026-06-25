<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Taxonomy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Content::query()->withCount('purchases')->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('title', 'like', "%{$q}%")
                    ->orWhere('country', 'like', "%{$q}%");
            });
        }

        return view('admin.contents.index', [
            'contents' => $query->paginate(12)->withQueryString(),
            'types' => Content::typeLabels(),
        ]);
    }

    public function create(): View
    {
        return view('admin.contents.form', [
            'content' => new Content(['status' => 'draft', 'access' => 'free', 'type' => 'video']),
            'types' => Content::typeLabels(),
            'themes' => Content::themeLabels(),
            'categories' => Content::categoryLabels(),
            'translations' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['user_id'] = auth()->id();
        $data['slug'] = Content::generateSlug($data['title']);

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $data = $this->handleUploads($request, $data);

        $content = Content::create($data);
        $this->saveTranslations($content, $request);

        return redirect()->route('admin.contents.index')
            ->with('success', 'Contenu publié avec succès.');
    }

    public function edit(Content $content): View
    {
        $content->load('translations');

        return view('admin.contents.form', [
            'content' => $content,
            'types' => Content::typeLabels(),
            'themes' => Content::themeLabels(),
            'categories' => Content::categoryLabels(),
            'translations' => $content->translationMap(),
        ]);
    }

    public function update(Request $request, Content $content): RedirectResponse
    {
        $data = $this->validated($request, $content->id);

        if ($data['status'] === 'published' && ! $content->published_at) {
            $data['published_at'] = now();
        }

        if ($data['status'] === 'draft') {
            $data['published_at'] = null;
        }

        $data = $this->handleUploads($request, $data, $content);

        $content->update($data);
        $this->saveTranslations($content, $request);

        return redirect()->route('admin.contents.index')
            ->with('success', 'Contenu mis à jour.');
    }

    public function destroy(Content $content): RedirectResponse
    {
        if ($content->thumbnail) {
            Storage::disk('public')->delete($content->thumbnail);
        }

        if ($content->media_path && ! str_starts_with($content->media_path, 'http')) {
            Storage::disk('public')->delete($content->media_path);
        }

        if ($content->preview_media_path && ! str_starts_with($content->preview_media_path, 'http')) {
            Storage::disk('public')->delete($content->preview_media_path);
        }

        $content->delete();

        return redirect()->route('admin.contents.index')
            ->with('success', 'Contenu supprimé.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'body' => ['nullable', 'string'],
            'type' => ['required', Rule::exists('taxonomies', 'slug')->where('kind', Taxonomy::KIND_TYPE)->where('is_active', true)],
            'theme' => ['nullable', Rule::exists('taxonomies', 'slug')->where('kind', Taxonomy::KIND_THEME)->where('is_active', true)],
            'country' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', Rule::exists('taxonomies', 'slug')->where('kind', Taxonomy::KIND_CATEGORY)->where('is_active', true)],
            'access' => ['required', 'in:free,subscriber,exclusive'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'price_gnf' => ['nullable', 'integer', 'min:0'],
            'duration' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:draft,published'],
            'preview_enabled' => ['nullable', 'boolean'],
            'preview_seconds' => ['nullable', 'integer', 'min:5', 'max:120'],
            'preview_excerpt' => ['nullable', 'string', 'max:2000'],
            'preview_media_file' => ['nullable', 'file', 'max:512000'],
            'preview_media_url' => ['nullable', 'url', 'max:500'],
            'thumbnail' => ['nullable', 'image', 'max:5120'],
            'media_file' => ['nullable', 'file', 'max:512000'],
            'media_url' => ['nullable', 'url', 'max:500'],
        ]);

        if ($data['access'] === 'exclusive' && (empty($data['price']) || (float) $data['price'] <= 0)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'price' => 'Un prix est obligatoire pour une exclusivité vendue à un seul acheteur.',
            ]);
        }

        if ((float) ($data['price'] ?? 0) > 0 && empty($data['price_gnf'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'price_gnf' => 'Indiquez le prix en GNF pour le paiement Djomy.',
            ]);
        }

        return $data;
    }

    private function handleUploads(Request $request, array $data, ?Content $content = null): array
    {
        if ($request->hasFile('thumbnail')) {
            if ($content?->thumbnail) {
                Storage::disk('public')->delete($content->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('contents/thumbnails', 'public');
        } else {
            unset($data['thumbnail']);
        }

        if ($request->hasFile('media_file')) {
            if ($content?->media_path && ! str_starts_with($content->media_path, 'http')) {
                Storage::disk('public')->delete($content->media_path);
            }
            $data['media_path'] = $request->file('media_file')->store('contents/media', 'public');
        } elseif ($request->filled('media_url')) {
            $data['media_path'] = $request->input('media_url');
        } else {
            unset($data['media_path']);
        }

        unset($data['media_file'], $data['media_url']);

        $data['preview_enabled'] = $request->boolean('preview_enabled');

        if ($request->hasFile('preview_media_file')) {
            if ($content?->preview_media_path && ! str_starts_with($content->preview_media_path, 'http')) {
                Storage::disk('public')->delete($content->preview_media_path);
            }
            $data['preview_media_path'] = $request->file('preview_media_file')->store('contents/previews', 'public');
        } elseif ($request->filled('preview_media_url')) {
            $data['preview_media_path'] = $request->input('preview_media_url');
        } else {
            unset($data['preview_media_path']);
        }

        unset($data['preview_media_file'], $data['preview_media_url']);

        return $data;
    }

    private function saveTranslations(Content $content, Request $request): void
    {
        $fields = ['title', 'summary', 'body', 'preview_excerpt'];

        foreach (['en', 'pt'] as $locale) {
            $input = $request->input("translations.{$locale}", []);

            if (! is_array($input)) {
                continue;
            }

            $content->saveTranslations($locale, array_intersect_key($input, array_flip($fields)));
        }
    }
}
