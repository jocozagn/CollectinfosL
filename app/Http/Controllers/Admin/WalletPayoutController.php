<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletPayoutRequest;
use App\Services\NotificationService;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WalletPayoutController extends Controller
{
    public function __construct(
        private WalletService $wallet,
        private NotificationService $notifications,
    ) {}

    public function index(Request $request): View
    {
        $query = WalletPayoutRequest::query()->with('user')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return view('admin.wallet-payouts.index', [
            'requests' => $query->paginate(20)->withQueryString(),
            'statuses' => [
                WalletPayoutRequest::STATUS_PENDING => 'En attente',
                WalletPayoutRequest::STATUS_PAID => 'Payées',
                WalletPayoutRequest::STATUS_REJECTED => 'Refusées',
            ],
        ]);
    }

    public function show(WalletPayoutRequest $payout): View
    {
        $payout->load(['user', 'processor']);

        return view('admin.wallet-payouts.show', [
            'payout' => $payout,
            'paymentMethods' => config('collectinfos.payment.methods', []),
        ]);
    }

    public function update(Request $request, WalletPayoutRequest $payout): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:pay,reject'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($data['action'] === 'pay') {
            $this->wallet->markPayoutPaid($payout, $request->user(), $data['admin_note'] ?? null);

            $payout->refresh();
            $this->notifications->notify(
                $payout->user,
                'payout_paid',
                'Reversement effectué',
                'Votre reversement de '.number_format($payout->amount, 0).' € a été traité.',
                route('account', ['tab' => 'wallet'])
            );

            $message = 'Reversement marqué comme payé.';
        } else {
            $request->validate(['admin_note' => ['required', 'string', 'max:2000']]);

            $this->wallet->rejectPayout($payout, $request->user(), $data['admin_note']);

            $payout->refresh();
            $this->notifications->notify(
                $payout->user,
                'payout_rejected',
                'Reversement refusé',
                'Votre demande a été refusée : '.$data['admin_note'],
                route('account', ['tab' => 'wallet'])
            );

            $message = 'Demande de reversement refusée.';
        }

        return redirect()
            ->route('admin.wallet-payouts.show', $payout)
            ->with('success', $message);
    }
}
