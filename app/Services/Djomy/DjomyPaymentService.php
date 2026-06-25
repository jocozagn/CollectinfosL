<?php

namespace App\Services\Djomy;

use App\Models\PaymentTransaction;
use App\Models\User;
use App\Services\CartService;
use App\Services\CurrencyService;
use App\Services\PaymentService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DjomyPaymentService
{
    public function __construct(
        private DjomyClient $client,
        private PaymentService $payments,
        private CartService $cart,
        private CurrencyService $currency,
        private SubscriptionService $subscriptions,
    ) {}

    public function usesDjomy(string $method): bool
    {
        return array_key_exists($method, config('djomy.method_map', []));
    }

    public function initiateCheckout(User $user, Collection $items, string $method, ?string $payerPhone = null): PaymentTransaction
    {
        $total = $items->sum(fn ($item) => $this->subscriptions->effectiveEurPrice($item, $user));
        $reference = 'CI-'.Str::upper(Str::random(12));
        $djomyAmount = $this->currency->cartTotalGnf($items, $user);
        $rate = $this->currency->eurToGnfRate();

        $transaction = PaymentTransaction::create([
            'user_id' => $user->id,
            'type' => PaymentTransaction::TYPE_CART,
            'reference' => $reference,
            'payment_method' => $method,
            'amount' => $total,
            'currency' => $this->currency->paymentCurrency(),
            'djomy_amount' => $djomyAmount,
            'status' => PaymentTransaction::STATUS_PENDING,
            'cart_snapshot' => [
                'content_ids' => $items->pluck('id')->values()->all(),
            ],
            'metadata' => [
                'eur_amount' => $total,
                'gnf_amount' => $djomyAmount,
                'eur_to_gnf_rate' => $rate,
            ],
        ]);

        $djomyMethod = config('djomy.method_map.'.$method);
        $payerNumber = $this->normalizePayerNumber($payerPhone ?: $user->phone);

        if ($payerNumber === '') {
            throw new \InvalidArgumentException('Numéro de téléphone payeur obligatoire pour Djomy.');
        }

        $gateway = $this->client->createPaymentGateway([
            'amount' => $djomyAmount,
            'countryCode' => config('djomy.country_code', 'GN'),
            'payerNumber' => $payerNumber,
            'allowedPaymentMethods' => [$djomyMethod],
            'description' => 'Achat Collectinfos — '.$items->count().' contenu(s)',
            'merchantPaymentReference' => $reference,
            'returnUrl' => $this->callbackUrl('payments.djomy.return', $reference),
            'cancelUrl' => $this->callbackUrl('payments.djomy.cancel', $reference),
            'metadata' => [
                'user_id' => $user->id,
                'reference' => $reference,
            ],
        ]);

        $transaction->update([
            'djomy_transaction_id' => $gateway['transactionId'] ?? $gateway['id'] ?? null,
            'redirect_url' => $gateway['redirectUrl'] ?? $gateway['paymentUrl'] ?? $gateway['url'] ?? null,
            'status' => PaymentTransaction::STATUS_REDIRECTED,
            'metadata' => array_merge($transaction->metadata ?? [], ['gateway' => $gateway]),
        ]);

        return $transaction->fresh();
    }

    public function initiateSubscriptionCheckout(
        User $user,
        \App\Models\SiteProduct $product,
        string $method,
        ?string $payerPhone = null,
    ): PaymentTransaction {
        if (! $product->isSubscribable()) {
            throw new \InvalidArgumentException('Cette offre n\'est pas disponible en abonnement en ligne.');
        }

        $total = (float) $product->price_eur;
        $reference = 'SUB-'.Str::upper(Str::random(12));
        $djomyAmount = $product->gnfAmount();

        $transaction = PaymentTransaction::create([
            'user_id' => $user->id,
            'type' => PaymentTransaction::TYPE_SUBSCRIPTION,
            'reference' => $reference,
            'payment_method' => $method,
            'amount' => $total,
            'currency' => $this->currency->paymentCurrency(),
            'djomy_amount' => $djomyAmount,
            'status' => PaymentTransaction::STATUS_PENDING,
            'cart_snapshot' => [
                'site_product_id' => $product->id,
            ],
            'metadata' => [
                'eur_amount' => $total,
                'gnf_amount' => $djomyAmount,
                'product_name' => $product->name,
            ],
        ]);

        $djomyMethod = config('djomy.method_map.'.$method);
        $payerNumber = $this->normalizePayerNumber($payerPhone ?: $user->phone);

        if ($payerNumber === '') {
            throw new \InvalidArgumentException('Numéro de téléphone payeur obligatoire pour Djomy.');
        }

        $gateway = $this->client->createPaymentGateway([
            'amount' => $djomyAmount,
            'countryCode' => config('djomy.country_code', 'GN'),
            'payerNumber' => $payerNumber,
            'allowedPaymentMethods' => [$djomyMethod],
            'description' => 'Abonnement Collectinfos — '.$product->name,
            'merchantPaymentReference' => $reference,
            'returnUrl' => $this->callbackUrl('payments.djomy.return', $reference),
            'cancelUrl' => $this->callbackUrl('payments.djomy.cancel', $reference),
            'metadata' => [
                'user_id' => $user->id,
                'reference' => $reference,
                'type' => 'subscription',
            ],
        ]);

        $transaction->update([
            'djomy_transaction_id' => $gateway['transactionId'] ?? $gateway['id'] ?? null,
            'redirect_url' => $gateway['redirectUrl'] ?? $gateway['paymentUrl'] ?? $gateway['url'] ?? null,
            'status' => PaymentTransaction::STATUS_REDIRECTED,
            'metadata' => array_merge($transaction->metadata ?? [], ['gateway' => $gateway]),
        ]);

        return $transaction->fresh();
    }

    public function fulfillIfPaid(PaymentTransaction $transaction): bool
    {
        if ($transaction->status === PaymentTransaction::STATUS_COMPLETED) {
            return true;
        }

        if (! in_array($transaction->status, [PaymentTransaction::STATUS_PENDING, PaymentTransaction::STATUS_REDIRECTED], true)) {
            return false;
        }

        if (! $transaction->djomy_transaction_id) {
            return false;
        }

        $status = $this->client->getPaymentStatus($transaction->djomy_transaction_id);

        if (($status['status'] ?? '') !== 'SUCCESS') {
            if (in_array($status['status'] ?? '', ['FAILED', 'CANCELLED', 'TIMEOUT'], true)) {
                $transaction->update(['status' => PaymentTransaction::STATUS_FAILED]);
            }

            return false;
        }

        return DB::transaction(function () use ($transaction, $status) {
            $transaction = PaymentTransaction::query()->lockForUpdate()->find($transaction->id);

            if ($transaction->status === PaymentTransaction::STATUS_COMPLETED) {
                return true;
            }

            $user = $transaction->user;

            if ($transaction->isSubscription()) {
                $this->subscriptions->fulfillTransaction($transaction);

                $transaction->update([
                    'status' => PaymentTransaction::STATUS_COMPLETED,
                    'paid_at' => now(),
                    'metadata' => array_merge($transaction->metadata ?? [], ['verified' => $status]),
                ]);

                return true;
            }

            $items = $this->cart->itemsFromIds($transaction->contentIds());

            $this->payments->processCheckout(
                $user,
                $items,
                $transaction->payment_method,
                $transaction->reference,
                $status['transactionId'] ?? $transaction->djomy_transaction_id
            );

            $transaction->update([
                'status' => PaymentTransaction::STATUS_COMPLETED,
                'paid_at' => now(),
                'metadata' => array_merge($transaction->metadata ?? [], ['verified' => $status]),
            ]);

            $this->cart->clear();

            return true;
        });
    }

    private function callbackUrl(string $routeName, string $reference): string
    {
        $base = rtrim((string) config('djomy.callback_base_url', config('app.url')), '/');

        if (! str_starts_with($base, 'https://')) {
            throw new \RuntimeException(
                'Djomy exige des URLs de retour en HTTPS. Définissez DJOMY_CALLBACK_BASE_URL=https://votre-domaine.com dans .env (ou utilisez ngrok en local).'
            );
        }

        return $base.route($routeName, $reference, false);
    }

    private function normalizePayerNumber(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '224')) {
            return '00'.$digits;
        }

        return '00224'.$digits;
    }
}
