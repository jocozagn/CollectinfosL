<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Investigation;
use App\Models\Taxonomy;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InvestigationController extends Controller
{
    public function index(): View
    {
        return view('admin.investigations.index', [
            'investigations' => Investigation::query()->with('owner')->latest('published_at')->paginate(12),
        ]);
    }

    public function create(): View
    {
        return view('admin.investigations.form', [
            'investigation' => new Investigation(['status' => 'open', 'places' => 3]),
            'themes' => Content::themeLabels(),
            'journalists' => User::query()->where('role', 'journalist')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Investigation::generateSlug($data['title']);
        $data['published_at'] = $data['status'] === Investigation::STATUS_OPEN ? now() : null;

        Investigation::create($data);

        return redirect()->route('admin.investigations.index')
            ->with('success', 'Enquête créée.');
    }

    public function edit(Investigation $investigation): View
    {
        return view('admin.investigations.form', [
            'investigation' => $investigation,
            'themes' => Content::themeLabels(),
            'journalists' => User::query()->where('role', 'journalist')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Investigation $investigation): RedirectResponse
    {
        $data = $this->validated($request);

        if ($data['status'] === Investigation::STATUS_OPEN && ! $investigation->published_at) {
            $data['published_at'] = now();
        }

        if ($data['status'] !== Investigation::STATUS_OPEN) {
            $data['published_at'] = $investigation->published_at;
        }

        if ($data['status'] === Investigation::STATUS_PENDING) {
            $data['published_at'] = null;
        }

        $investigation->update($data);

        return redirect()->route('admin.investigations.index')
            ->with('success', 'Enquête mise à jour.');
    }

    public function destroy(Investigation $investigation): RedirectResponse
    {
        $investigation->delete();

        return redirect()->route('admin.investigations.index')
            ->with('success', 'Enquête supprimée.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'user_id' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string', 'max:2000'],
            'country' => ['nullable', 'string', 'max:100'],
            'theme' => ['nullable', Rule::exists('taxonomies', 'slug')->where('kind', Taxonomy::KIND_THEME)->where('is_active', true)],
            'places' => ['required', 'integer', 'min:1', 'max:50'],
            'status' => ['required', 'in:open,closed,pending'],
        ]);
    }
}
