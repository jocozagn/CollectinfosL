<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class JournalistProfileController extends Controller
{
    public function show(string $slug): View
    {
        $journalist = User::query()
            ->where('profile_slug', $slug)
            ->where('role', 'journalist')
            ->firstOrFail();

        $contents = $journalist->contents()
            ->published()
            ->latest('published_at')
            ->take(12)
            ->get();

        return view('pages.journalist-profile', [
            'journalist' => $journalist,
            'contents' => $contents,
        ]);
    }
}
