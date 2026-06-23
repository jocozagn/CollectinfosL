<?php

namespace App\Http\Controllers;

use App\Models\ContentPurchase;
use App\Models\Investigation;
use App\Models\InvestigationParticipant;
use App\Models\CollaborationRequest;
use App\Models\Content;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(Request $request): View
    {
        if (! Auth::check()) {
            return view('pages.account');
        }

        $user = Auth::user();
        $tab = $request->query('tab', $user->isJournalist() ? 'investigations' : 'purchases');

        if (! $user->isJournalist() && in_array($tab, ['investigations', 'applications', 'participations'], true)) {
            $tab = 'purchases';
        }

        $purchases = ContentPurchase::query()
            ->with('content')
            ->where('user_id', $user->id)
            ->latest('purchased_at')
            ->get();

        $ownedInvestigations = collect();
        $applications = collect();
        $participations = collect();
        $themes = [];

        if ($user->isJournalist()) {
            $ownedInvestigations = Investigation::query()
                ->where('user_id', $user->id)
                ->latest()
                ->get();

            $applications = CollaborationRequest::query()
                ->with('investigation')
                ->where(function ($query) use ($user) {
                    $query->where('user_id', $user->id)
                        ->orWhere('email', $user->email);
                })
                ->latest()
                ->get();

            $participationIds = InvestigationParticipant::query()
                ->where('user_id', $user->id)
                ->pluck('investigation_id');

            $participations = Investigation::query()
                ->with('owner')
                ->whereIn('id', $participationIds)
                ->where(function ($query) use ($user) {
                    $query->whereNull('user_id')->orWhere('user_id', '!=', $user->id);
                })
                ->latest('updated_at')
                ->get();

            $themes = Content::themeLabels();
        }

        return view('pages.account-dashboard', [
            'user' => $user,
            'tab' => $tab,
            'purchases' => $purchases,
            'ownedInvestigations' => $ownedInvestigations,
            'applications' => $applications,
            'participations' => $participations,
            'themes' => $themes,
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (Auth::user()->isAdmin()) {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'Utilisez l\'espace admin pour ce compte.',
                ])->onlyInput('email');
            }

            return redirect()->intended(route('account'));
        }

        return back()->withErrors([
            'email' => 'Identifiants incorrects.',
        ])->onlyInput('email');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'journalist' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $request->boolean('journalist') ? 'journalist' : 'user',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $message = $user->isJournalist()
            ? 'Bienvenue ! Votre compte journaliste a été créé. Vous pouvez proposer ou rejoindre des enquêtes.'
            : 'Bienvenue ! Votre compte a été créé.';

        return redirect()->route('account')->with('account_success', $message);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
