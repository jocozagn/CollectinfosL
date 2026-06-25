<?php

namespace App\Services;

use App\Models\Content;
use App\Models\PaymentTransaction;
use App\Models\SiteProduct;
use App\Models\User;
use App\Models\UserSubscription;

class SubscriptionService
{
    public function __construct(
        private CurrencyService $currency,
        private NotificationService $notifications,
    ) {}

    public function effectiveEurPrice(Content $content, ?User $user = null): float
    {
        $base = (float) $content->price;

        if ($base <= 0 || ! $user) {
            return $base;
        }

        $discount = $user->subscriptionDiscountPercent();

        if ($discount <= 0) {
            return $base;
        }

        return round($base * (1 - $discount / 100), 2);
    }

    public function effectiveGnfPrice(Content $content, ?User $user = null): int
    {
        $eur = $this->effectiveEurPrice($content, $user);

        if ($content->hasManualGnfPrice() && $user && $user->subscriptionDiscountPercent() > 0) {
            $baseGnf = (int) $content->price_gnf;
            $discount = $user->subscriptionDiscountPercent();

            return (int) round($baseGnf * (1 - $discount / 100));
        }

        if ($content->hasManualGnfPrice()) {
            return (int) $content->price_gnf;
        }

        return $this->currency->eurToGnf($eur);
    }

    public function activate(
        User $user,
        SiteProduct $product,
        string $method,
        string $reference,
        ?int $paymentTransactionId = null,
    ): UserSubscription {
        $startsAt = now();
        $months = max(1, (int) $product->billing_months);

        $this->subscriptions($user)
            ->where('status', UserSubscription::STATUS_ACTIVE)
            ->where('ends_at', '>', $startsAt)
            ->update(['status' => UserSubscription::STATUS_CANCELLED]);

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'site_product_id' => $product->id,
            'status' => UserSubscription::STATUS_ACTIVE,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->copy()->addMonths($months),
            'payment_method' => $method,
            'payment_reference' => $reference,
            'price_eur' => $product->price_eur,
            'price_gnf' => $product->gnfAmount(),
            'payment_transaction_id' => $paymentTransactionId,
        ]);

        $this->notifications->notify(
            $user,
            'subscription',
            'Abonnement activé',
            'Votre abonnement « '.$product->name.' » est actif jusqu\'au '.$subscription->ends_at->format('d/m/Y').'.',
            route('account', ['tab' => 'billing'])
        );

        return $subscription;
    }

    public function fulfillTransaction(PaymentTransaction $transaction): bool
    {
        $productId = $transaction->subscriptionProductId();

        if (! $productId) {
            return false;
        }

        $product = SiteProduct::query()->find($productId);

        if (! $product || ! $product->isSubscribable()) {
            return false;
        }

        $exists = UserSubscription::query()
            ->where('payment_transaction_id', $transaction->id)
            ->exists();

        if ($exists) {
            return true;
        }

        $this->activate(
            $transaction->user,
            $product,
            $transaction->payment_method,
            $transaction->reference,
            $transaction->id
        );

        return true;
    }

    private function subscriptions(User $user)
    {
        return UserSubscription::query()->where('user_id', $user->id);
    }
}
