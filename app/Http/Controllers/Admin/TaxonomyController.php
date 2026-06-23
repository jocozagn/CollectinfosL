<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use App\Models\Taxonomy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TaxonomyController extends Controller
{
    public function index(string $kind): View
    {
        $taxonomyKind = $this->resolveKind($kind);

        return view('admin.taxonomies.index', [
            'kind' => $taxonomyKind,
            'kindRoute' => $kind,
            'kindLabel' => Taxonomy::kindLabels()[$taxonomyKind],
            'items' => Taxonomy::query()
                ->where('kind', $taxonomyKind)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(20),
        ]);
    }

    public function create(string $kind): View
    {
        return view('admin.taxonomies.form', [
            'kind' => $this->resolveKind($kind),
            'kindRoute' => $kind,
            'kindLabel' => Taxonomy::kindLabels()[$this->resolveKind($kind)],
            'taxonomy' => new Taxonomy(['is_active' => true, 'show_on_home' => false, 'sort_order' => 0]),
            'translations' => [],
        ]);
    }

    public function store(Request $request, string $kind): RedirectResponse
    {
        $taxonomyKind = $this->resolveKind($kind);
        $data = $this->validated($request, $taxonomyKind);
        $data['kind'] = $taxonomyKind;
        $data['slug'] = Taxonomy::generateSlug($data['name'], $taxonomyKind);
        $data = $this->handleImage($request, $data);

        $taxonomy = Taxonomy::create($data);
        $this->saveTranslations($taxonomy, $request);

        return redirect()->route('admin.taxonomies.index', $kind)
            ->with('success', 'Élément ajouté.');
    }

    public function edit(string $kind, Taxonomy $taxonomy): View
    {
        $this->ensureKind($kind, $taxonomy);
        $taxonomy->load('translations');

        return view('admin.taxonomies.form', [
            'kind' => $taxonomy->kind,
            'kindRoute' => $kind,
            'kindLabel' => $taxonomy->kindLabel(),
            'taxonomy' => $taxonomy,
            'translations' => $taxonomy->translationMap(),
        ]);
    }

    public function update(Request $request, string $kind, Taxonomy $taxonomy): RedirectResponse
    {
        $this->ensureKind($kind, $taxonomy);

        $data = $this->validated($request, $taxonomy->kind, $taxonomy->id);

        if ($data['name'] !== $taxonomy->name) {
            $data['slug'] = Taxonomy::generateSlug($data['name'], $taxonomy->kind, $taxonomy->id);
        }

        $data = $this->handleImage($request, $data, $taxonomy);
        $taxonomy->update($data);
        $this->saveTranslations($taxonomy, $request);

        return redirect()->route('admin.taxonomies.index', $kind)
            ->with('success', 'Élément mis à jour.');
    }

    public function destroy(string $kind, Taxonomy $taxonomy): RedirectResponse
    {
        $this->ensureKind($kind, $taxonomy);

        if ($taxonomy->isInUse()) {
            return back()->with('error', 'Cet élément est utilisé par des contenus et ne peut pas être supprimé.');
        }

        if ($taxonomy->image && ! str_starts_with($taxonomy->image, 'http')) {
            Storage::disk('public')->delete($taxonomy->image);
        }

        $taxonomy->translations()->delete();
        $taxonomy->delete();

        return redirect()->route('admin.taxonomies.index', $kind)
            ->with('success', 'Élément supprimé.');
    }

    private function resolveKind(string $kind): string
    {
        $resolved = Taxonomy::kindFromRoute($kind);

        abort_if($resolved === null, 404);

        return $resolved;
    }

    private function ensureKind(string $kind, Taxonomy $taxonomy): void
    {
        abort_unless($taxonomy->kind === $this->resolveKind($kind), 404);
    }

    private function validated(Request $request, string $kind, ?int $ignoreId = null): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:5120'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
        ];

        if ($kind === Taxonomy::KIND_CATEGORY) {
            $rules['show_on_home'] = ['nullable', 'boolean'];
        }

        $data = $request->validate($rules);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['show_on_home'] = $kind === Taxonomy::KIND_CATEGORY
            ? $request->boolean('show_on_home')
            : false;
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }

    private function handleImage(Request $request, array $data, ?Taxonomy $taxonomy = null): array
    {
        if ($request->hasFile('image')) {
            if ($taxonomy?->image && ! str_starts_with($taxonomy->image, 'http')) {
                Storage::disk('public')->delete($taxonomy->image);
            }
            $data['image'] = $request->file('image')->store('taxonomies', 'public');
        } elseif ($request->filled('image_url')) {
            $data['image'] = $request->input('image_url');
        } else {
            unset($data['image']);
        }

        unset($data['image_url']);

        return $data;
    }

    private function saveTranslations(Taxonomy $taxonomy, Request $request): void
    {
        foreach (['en', 'pt'] as $locale) {
            $input = $request->input("translations.{$locale}", []);

            if (! is_array($input)) {
                continue;
            }

            $taxonomy->saveTranslations($locale, array_intersect_key($input, array_flip(['name', 'description'])));
        }
    }
}
