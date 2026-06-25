<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JournalistProfileController extends Controller
{
    public function __construct(private ProfileService $profiles) {}

    public function index(Request $request): View
    {
        $query = User::query()
            ->where('role', 'journalist')
            ->orderByDesc('created_at');

        if ($request->filled('verified')) {
            $request->verified === '1'
                ? $query->whereNotNull('profile_verified_at')
                : $query->whereNull('profile_verified_at');
        }

        $journalists = $query->paginate(20)->withQueryString();

        return view('admin.journalist-profiles.index', [
            'journalists' => $journalists,
            'profiles' => $this->profiles,
        ]);
    }

    public function verify(User $user): RedirectResponse
    {
        abort_unless($user->isJournalist(), 404);

        $user->update(['profile_verified_at' => now()]);

        return back()->with('success', 'Profil journaliste validé.');
    }

    public function unverify(User $user): RedirectResponse
    {
        abort_unless($user->isJournalist(), 404);

        $user->update(['profile_verified_at' => null]);

        return back()->with('success', 'Validation du profil retirée.');
    }
}
