<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Investigation;
use App\Models\Taxonomy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class JournalistInvestigationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user || ! $user->isJournalist()) {
            abort(403);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string', 'max:2000'],
            'country' => ['nullable', 'string', 'max:100'],
            'theme' => ['nullable', Rule::exists('taxonomies', 'slug')->where('kind', Taxonomy::KIND_THEME)->where('is_active', true)],
            'places' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        Investigation::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'slug' => Investigation::generateSlug($data['title']),
            'summary' => $data['summary'],
            'country' => $data['country'] ?? null,
            'theme' => $data['theme'] ?? null,
            'places' => $data['places'],
            'status' => Investigation::STATUS_PENDING,
            'published_at' => null,
        ]);

        return redirect()->route('account', ['tab' => 'investigations'])
            ->with('account_success', 'Votre enquête a été soumise. Elle sera publiée après validation par l\'équipe Collectinfos.');
    }
}
