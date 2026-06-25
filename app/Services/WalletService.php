<?php

namespace App\Services;

use App\Models\ContentPurchase;
use App\Models\User;
use App\Models\WalletPayoutRequest;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService
{
    public function creditFromSale(User $journalist, ContentPurchase $purchase): void
    {
        $purchase->loadMissing('content');

        if ($purchase->journalist_earning <= 0) {
            return;
        }

        DB::transaction(function () use ($journalist, $purchase) {
            $user = User::query()->lockForUpdate()->find($journalist->id);
            $user->wallet_balance = (float) $user->wallet_balance + (float) $purchase->journalist_earning;
            $user->save();

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => WalletTransaction::TYPE_SALE_CREDIT,
                'amount' => $purchase->journalist_earning,
                'balance_after' => $user->wallet_balance,
                'reference_type' => ContentPurchase::class,
                'reference_id' => $purchase->id,
                'description' => 'Vente : '.$purchase->content->title,
            ]);
        });
    }

    public function minPayoutAmount(): float
    {
        return (float) config('collectinfos.wallet.min_payout_eur', 50);
    }

    public function requestPayout(User $journalist, float $amount, string $method, ?string $phone = null, ?string $details = null): WalletPayoutRequest
    {
        if (! $journalist->isJournalist()) {
            throw ValidationException::withMessages([
                'amount' => 'Seuls les journalistes peuvent demander un reversement.',
            ]);
        }

        $min = $this->minPayoutAmount();

        if ($amount < $min) {
            throw ValidationException::withMessages([
                'amount' => 'Le montant minimum de reversement est de '.number_format($min, 0).' €.',
            ]);
        }

        if ($amount > (float) $journalist->wallet_balance) {
            throw ValidationException::withMessages([
                'amount' => 'Montant supérieur à votre solde disponible.',
            ]);
        }

        if (WalletPayoutRequest::query()
            ->where('user_id', $journalist->id)
            ->where('status', WalletPayoutRequest::STATUS_PENDING)
            ->exists()) {
            throw ValidationException::withMessages([
                'amount' => 'Vous avez déjà une demande de reversement en attente.',
            ]);
        }

        $mobileMethods = ['orange_money', 'wave', 'mtn'];

        if (in_array($method, $mobileMethods, true) && ! $phone) {
            throw ValidationException::withMessages([
                'payout_phone' => 'Indiquez votre numéro mobile money.',
            ]);
        }

        return WalletPayoutRequest::create([
            'user_id' => $journalist->id,
            'amount' => $amount,
            'method' => $method,
            'payout_phone' => $phone,
            'payout_details' => $details,
            'status' => WalletPayoutRequest::STATUS_PENDING,
        ]);
    }

    public function markPayoutPaid(WalletPayoutRequest $request, User $admin, ?string $note = null): void
    {
        DB::transaction(function () use ($request, $admin, $note) {
            $request = WalletPayoutRequest::query()->lockForUpdate()->findOrFail($request->id);

            if (! $request->isPending()) {
                throw ValidationException::withMessages([
                    'status' => 'Cette demande a déjà été traitée.',
                ]);
            }

            $user = User::query()->lockForUpdate()->findOrFail($request->user_id);

            if ((float) $request->amount > (float) $user->wallet_balance) {
                throw ValidationException::withMessages([
                    'status' => 'Solde journaliste insuffisant pour ce reversement.',
                ]);
            }

            $user->wallet_balance = (float) $user->wallet_balance - (float) $request->amount;
            $user->save();

            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => WalletTransaction::TYPE_PAYOUT,
                'amount' => -1 * (float) $request->amount,
                'balance_after' => $user->wallet_balance,
                'reference_type' => WalletPayoutRequest::class,
                'reference_id' => $request->id,
                'description' => 'Reversement '.$request->method,
            ]);

            $request->update([
                'status' => WalletPayoutRequest::STATUS_PAID,
                'processed_by' => $admin->id,
                'processed_at' => now(),
                'admin_note' => $note,
            ]);
        });
    }

    public function rejectPayout(WalletPayoutRequest $request, User $admin, string $note): void
    {
        DB::transaction(function () use ($request, $admin, $note) {
            $request = WalletPayoutRequest::query()->lockForUpdate()->findOrFail($request->id);

            if (! $request->isPending()) {
                throw ValidationException::withMessages([
                    'status' => 'Cette demande a déjà été traitée.',
                ]);
            }

            $request->update([
                'status' => WalletPayoutRequest::STATUS_REJECTED,
                'processed_by' => $admin->id,
                'processed_at' => now(),
                'admin_note' => $note,
            ]);
        });
    }
}
