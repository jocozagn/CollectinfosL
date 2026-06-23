<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PartnerController extends Controller
{
    public function index(): View
    {
        return view('admin.partners.index', [
            'partners' => Partner::query()->orderBy('sort_order')->orderBy('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.partners.form', [
            'partner' => new Partner(['is_active' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Partner::create($this->validated($request));

        return redirect()->route('admin.partners.index')
            ->with('success', 'Partenaire ajouté.');
    }

    public function edit(Partner $partner): View
    {
        return view('admin.partners.form', ['partner' => $partner]);
    }

    public function update(Request $request, Partner $partner): RedirectResponse
    {
        $partner->update($this->validated($request, $partner));

        return redirect()->route('admin.partners.index')
            ->with('success', 'Partenaire mis à jour.');
    }

    public function destroy(Partner $partner): RedirectResponse
    {
        if ($partner->logo && ! str_starts_with($partner->logo, 'http')) {
            Storage::disk('public')->delete($partner->logo);
        }

        $partner->delete();

        return redirect()->route('admin.partners.index')
            ->with('success', 'Partenaire supprimé.');
    }

    private function validated(Request $request, ?Partner $partner = null): array
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'url' => ['nullable', 'url', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
            'logo' => [$partner ? 'nullable' : 'required_without:logo_url', 'image', 'max:5120'],
            'logo_url' => ['nullable', 'url', 'max:500'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        if ($request->hasFile('logo')) {
            if ($partner?->logo && ! str_starts_with($partner->logo, 'http')) {
                Storage::disk('public')->delete($partner->logo);
            }
            $data['logo'] = $request->file('logo')->store('partners', 'public');
        } elseif ($request->filled('logo_url')) {
            $data['logo'] = $request->input('logo_url');
        } elseif (! $partner) {
            $data['logo'] = $request->input('logo_url');
        } else {
            unset($data['logo']);
        }

        unset($data['logo_url']);

        return $data;
    }
}
