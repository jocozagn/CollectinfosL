<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteStat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteStatController extends Controller
{
    public function index(): View
    {
        return view('admin.site-stats.index', [
            'stats' => SiteStat::query()->orderBy('sort_order')->orderBy('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.site-stats.form', [
            'stat' => new SiteStat(['is_active' => true, 'sort_order' => 0, 'value' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        SiteStat::create($this->validated($request));

        return redirect()->route('admin.site-stats.index')
            ->with('success', 'Statistique ajoutée.');
    }

    public function edit(SiteStat $siteStat): View
    {
        return view('admin.site-stats.form', [
            'stat' => $siteStat,
        ]);
    }

    public function update(Request $request, SiteStat $siteStat): RedirectResponse
    {
        $siteStat->update($this->validated($request));

        return redirect()->route('admin.site-stats.index')
            ->with('success', 'Statistique mise à jour.');
    }

    public function destroy(SiteStat $siteStat): RedirectResponse
    {
        $siteStat->delete();

        return redirect()->route('admin.site-stats.index')
            ->with('success', 'Statistique supprimée.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'value' => ['required', 'integer', 'min:0'],
            'label' => ['required', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        return $data;
    }
}
