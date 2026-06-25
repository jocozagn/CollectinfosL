<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WalletPayoutController extends Controller
{
    public function __construct(
        private WalletService $wallet,
    ) {}

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $methods = ['orange_money', 'wave', 'mtn', 'bank_transfer'];

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'method' => ['required', Rule::in($methods)],
            'payout_phone' => ['nullable', 'string', 'max:30'],
            'payout_details' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->wallet->requestPayout(
            $user,
            (float) $data['amount'],
            $data['method'],
            $data['payout_phone'] ?? null,
            $data['payout_details'] ?? null,
        );

        return redirect()
            ->route('account', ['tab' => 'wallet'])
            ->with('account_success', 'Demande de reversement envoyée. Elle sera traitée sous peu.');
    }
}
