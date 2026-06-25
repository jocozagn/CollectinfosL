<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ContentPurchase;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(
        private NotificationService $notifications,
        private WalletService $wallet,
        private SubscriptionService $subscriptions,
    ) {}

    public function methods(): array
    {
        return config('collectinfos.payment.methods', []);
    }

    public function checkoutMethods(): array
    {
        return array_filter(
            $this->methods(),
            fn (array $method) => ! ($method['coming_soon'] ?? false)
        );
    }

    public function isCheckoutMethod(string $key): bool
    {
        return array_key_exists($key, $this->checkoutMethods());
    }

    public function commissionRate(): float
    {
        return (float) config('collectinfos.payment.commission_rate', 0.15);
    }

    public function djomyMethods(): array
    {
        return array_intersect_key(
            $this->checkoutMethods(),
            config('djomy.method_map', [])
        );
    }

    public function offlineMethods(): array
    {
        return array_diff_key($this->checkoutMethods(), config('djomy.method_map', []));
    }

    public function processCheckout(
        User $buyer,
        Collection $items,
        string $method,
        ?string $paymentReference = null,
        ?string $externalTransactionId = null,
    ): array {
        $purchased = 0;
        $unavailable = [];
        $total = 0;
        $reference = $paymentReference ?: strtoupper($method).'-'.Str::upper(Str::random(10));

        foreach ($items as $item) {
            $content = Content::query()->lockForUpdate()->find($item->id);

            if (! $content || $buyer->hasPurchased($content) || ! $content->isPurchasable()) {
                if ($content) {
                    $unavailable[] = $content->title;
                }

                continue;
            }

            $price = $this->subscriptions->effectiveEurPrice($content, $buyer);
            $fee = round($price * $this->commissionRate(), 2);
            $earning = round($price - $fee, 2);

            $purchase = ContentPurchase::create([
                'user_id' => $buyer->id,
                'content_id' => $content->id,
                'price' => $price,
                'purchased_at' => now(),
                'payment_method' => $method,
                'payment_status' => 'completed',
                'payment_reference' => $externalTransactionId ?: $reference,
                'invoice_number' => $this->nextInvoiceNumber(),
                'platform_fee' => $fee,
                'journalist_earning' => $earning,
            ]);

            if ($content->user_id) {
                $journalist = User::find($content->user_id);
                if ($journalist) {
                    $this->wallet->creditFromSale($journalist, $purchase);
                    $this->notifications->notify(
                        $journalist,
                        'sale',
                        'Nouvelle vente',
                        'Votre contenu « '.$content->title.' » a été acheté pour '.number_format($price, 0).' €.',
                        route('account', ['tab' => 'sales'])
                    );
                }
            }

            $this->notifications->notify(
                $buyer,
                'purchase',
                'Achat confirmé',
                'Vous avez acquis « '.$content->title.' ».',
                route('account', ['tab' => 'purchases'])
            );

            $purchased++;
            $total += $price;
        }

        return [
            'purchased' => $purchased,
            'unavailable' => $unavailable,
            'total' => $total,
            'reference' => $reference,
        ];
    }

    private function nextInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $count = ContentPurchase::query()
            ->whereYear('purchased_at', $year)
            ->whereNotNull('invoice_number')
            ->count() + 1;

        return 'INV-'.$year.'-'.str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }
}
