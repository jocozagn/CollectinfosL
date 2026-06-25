<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\ContentOrder;
use App\Models\ContentPurchase;
use App\Models\ContentSubmission;
use App\Models\Investigation;
use App\Models\InvestigationParticipant;
use App\Models\CollaborationRequest;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserSubscription;
use App\Models\WalletPayoutRequest;
use App\Models\WalletTransaction;
use App\Services\ContentSubmissionService;
use App\Services\NotificationService;
use App\Services\ProfileService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(
        private NotificationService $notifications,
        private ContentSubmissionService $submissionService,
        private WalletService $wallet,
        private ProfileService $profiles,
    ) {}

    public function index(Request $request): View
    {
        if (! Auth::check()) {
            return view('pages.account');
        }

        $user = Auth::user();
        $tab = $request->query('tab', $user->isJournalist() ? 'publications' : 'purchases');

        $journalistTabs = ['investigations', 'applications', 'participations', 'publications', 'sales', 'wallet', 'received_orders', 'profile'];
        $buyerTabs = ['purchases', 'orders', 'favorites', 'billing', 'notifications', 'profile'];

        if (! $user->isJournalist() && in_array($tab, $journalistTabs, true)) {
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
        $favorites = collect();
        $themes = [];
        $publications = collect();
        $sales = collect();
        $walletTransactions = collect();
        $payoutRequests = collect();
        $contentOrders = collect();
        $receivedOrders = collect();
        $notifications = collect();

        if ($user->isJournalist()) {
            $ownedInvestigations = Investigation::query()
                ->where('user_id', $user->id)
                ->withCount(['pendingJoinRequests as pending_candidatures_count'])
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

            $publications = ContentSubmission::query()
                ->where('user_id', $user->id)
                ->latest()
                ->get();

            $sales = ContentPurchase::query()
                ->with('content', 'user')
                ->whereHas('content', fn ($q) => $q->where('user_id', $user->id))
                ->latest('purchased_at')
                ->get();

            $walletTransactions = WalletTransaction::query()
                ->where('user_id', $user->id)
                ->latest()
                ->take(20)
                ->get();

            $payoutRequests = WalletPayoutRequest::query()
                ->where('user_id', $user->id)
                ->latest()
                ->take(10)
                ->get();

            $receivedOrders = ContentOrder::query()
                ->where('assigned_journalist_id', $user->id)
                ->latest()
                ->get();
        }

        $favorites = $user->favoriteContents()
            ->with('translations')
            ->get()
            ->map(fn (Content $content) => $content->toCardArray());

        $contentOrders = ContentOrder::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $notifications = UserNotification::query()
            ->where('user_id', $user->id)
            ->latest()
            ->take(30)
            ->get();

        $activeSubscription = $user->activeSubscription()?->load('product');
        $subscriptions = UserSubscription::query()
            ->with('product')
            ->where('user_id', $user->id)
            ->latest('starts_at')
            ->get();

        return view('pages.account-dashboard', [
            'user' => $user,
            'tab' => $tab,
            'purchases' => $purchases,
            'favorites' => $favorites,
            'ownedInvestigations' => $ownedInvestigations,
            'applications' => $applications,
            'participations' => $participations,
            'themes' => $themes,
            'publications' => $publications,
            'sales' => $sales,
            'walletTransactions' => $walletTransactions,
            'payoutRequests' => $payoutRequests,
            'minPayoutAmount' => $this->wallet->minPayoutAmount(),
            'contentOrders' => $contentOrders,
            'receivedOrders' => $receivedOrders,
            'notifications' => $notifications,
            'accountTypes' => config('collectinfos.account_types', []),
            'paymentMethods' => config('collectinfos.payment.methods', []),
            'unreadNotifications' => $this->notifications->unreadCount($user),
            'profileCompletion' => $this->profiles->completion($user),
            'activeSubscription' => $activeSubscription,
            'subscriptions' => $subscriptions,
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
        $accountTypes = array_keys(config('collectinfos.account_types', []));

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'account_type' => ['nullable', Rule::in($accountTypes)],
            'journalist' => ['nullable', 'boolean'],
            'specialties' => ['nullable', 'string', 'max:500'],
            'languages' => ['nullable', 'string', 'max:255'],
            'coverage_zones' => ['nullable', 'string', 'max:500'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'structure_type' => ['nullable', Rule::in(array_keys(config('collectinfos.buyer_structure_types', [])))],
            'editorial_themes' => ['nullable', 'string', 'max:500'],
        ]);

        $isJournalist = $request->boolean('journalist')
            || in_array($data['account_type'] ?? '', ['journalist', 'correspondent', 'photographer', 'videographer', 'expert'], true);

        $meta = array_filter([
            'specialties' => $data['specialties'] ?? null,
            'languages' => $data['languages'] ?? null,
            'coverage_zones' => $data['coverage_zones'] ?? null,
            'organization_name' => $data['organization_name'] ?? null,
            'structure_type' => $data['structure_type'] ?? null,
            'editorial_themes' => $data['editorial_themes'] ?? null,
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $isJournalist ? 'journalist' : 'user',
            'phone' => $data['phone'] ?? null,
            'country' => $data['country'] ?? null,
            'city' => $data['city'] ?? null,
            'account_type' => $data['account_type'] ?? ($isJournalist ? 'journalist' : 'media'),
            'profile_meta' => $meta ?: null,
        ]);

        if ($isJournalist) {
            $user->profile_slug = $this->submissionService->generateProfileSlug($user);
            $user->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        $message = $user->isJournalist()
            ? 'Bienvenue ! Complétez votre profil et déposez vos premiers contenus.'
            : 'Bienvenue ! Parcourez le catalogue ou commandez un sujet sur mesure.';

        return redirect()->route('account', ['tab' => 'profile'])->with('account_success', $message);
    }

    public function markNotificationsRead(Request $request): RedirectResponse
    {
        $this->notifications->markAllRead(Auth::user());

        return back()->with('account_success', 'Notifications marquées comme lues.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
