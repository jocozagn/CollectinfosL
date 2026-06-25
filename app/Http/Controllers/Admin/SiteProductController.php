<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index', [
            'products' => SiteProduct::query()->orderBy('sort_order')->orderBy('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new SiteProduct(['is_active' => true, 'sort_order' => 0, 'icon' => 'fa-newspaper']),
            'translations' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $product = SiteProduct::create($this->validated($request));
        $this->saveTranslations($product, $request);

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit ajouté.');
    }

    public function edit(SiteProduct $product): View
    {
        $product->load('translations');

        return view('admin.products.form', [
            'product' => $product,
            'translations' => $product->translationMap(),
        ]);
    }

    public function update(Request $request, SiteProduct $product): RedirectResponse
    {
        $product->update($this->validated($request));
        $this->saveTranslations($product, $request);

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit mis à jour.');
    }

    public function destroy(SiteProduct $product): RedirectResponse
    {
        $product->translations()->delete();
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Produit supprimé.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:2000'],
            'price' => ['required', 'string', 'max:80'],
            'icon' => ['required', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'is_subscribable' => ['nullable', 'boolean'],
            'price_eur' => ['nullable', 'numeric', 'min:0'],
            'price_gnf' => ['nullable', 'integer', 'min:0'],
            'billing_months' => ['nullable', 'integer', 'min:1', 'max:36'],
            'discount_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_subscribable'] = $request->boolean('is_subscribable', false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['billing_months'] = (int) ($data['billing_months'] ?? 1);
        $data['discount_percent'] = (int) ($data['discount_percent'] ?? 10);

        if ($data['is_subscribable'] && empty($data['price_eur'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'price_eur' => 'Indiquez le prix EUR pour un abonnement en ligne.',
            ]);
        }

        return $data;
    }

    private function saveTranslations(SiteProduct $product, Request $request): void
    {
        foreach (['en', 'pt'] as $locale) {
            $input = $request->input("translations.{$locale}", []);

            if (! is_array($input)) {
                continue;
            }

            $product->saveTranslations($locale, array_intersect_key($input, array_flip(['name', 'description'])));
        }
    }
}
