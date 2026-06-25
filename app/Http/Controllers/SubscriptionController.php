<?php

namespace App\Http\Controllers;

use App\Models\SiteProduct;
use App\Services\Djomy\DjomyClient;
use App\Services\Djomy\DjomyPaymentService;
use App\Services\PaymentService;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    public function __construct(
        private PaymentService $payments,
        private DjomyPaymentService $djomyPayments,
        private DjomyClient $djomyClient,
        private SubscriptionService $subscriptions,
    ) {}

    public function show(SiteProduct $product): View|RedirectResponse
    {
        if (! $product->is_active || ! $product->isSubscribable()) {
            return redirect()
                ->route('products')
                ->with('error', 'Cette offre n\'est pas disponible en ligne.');
        }

        $user = Auth::user();
        $active = $user?->activeSubscription();

        return view('pages.subscription-checkout', [
            'product' => $product,
            'activeSubscription' => $active,
            'paymentMethods' => $this->payments->methods(),
            'checkoutMethodKeys' => array_keys($this->payments->checkoutMethods()),
            'defaultPaymentMethod' => array_key_first($this->payments->checkoutMethods()),
            'djomyEnabled' => $this->djomyClient->isConfigured(),
            'djomyMethodKeys' => array_keys(config('djomy.method_map', [])),
            'currency' => app(\App\Services\CurrencyService::class),
        ]);
    }

    public function checkout(Request $request, SiteProduct $product): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()
                ->route('account')
                ->with('error', 'Connectez-vous pour souscrire un abonnement.');
        }

        if (! $product->is_active || ! $product->isSubscribable()) {
            return redirect()->route('products')->with('error', 'Offre indisponible.');
        }

        $methods = array_keys($this->payments->checkoutMethods());
        $data = $request->validate([
            'payment_method' => ['required', Rule::in($methods)],
            'payer_phone' => ['nullable', 'string', 'max:30'],
        ]);

        $user = Auth::user();
        $method = $data['payment_method'];

        if ($this->djomyClient->isConfigured() && $this->djomyPayments->usesDjomy($method)) {
            $phone = $data['payer_phone'] ?? $user->phone;

            if (! $phone) {
                return back()
                    ->withErrors(['payer_phone' => 'Indiquez votre numéro mobile money pour payer via Djomy.'])
                    ->withInput();
            }

            try {
                $transaction = $this->djomyPayments->initiateSubscriptionCheckout($user, $product, $method, $phone);
            } catch (\Throwable $exception) {
                return back()->with('error', 'Impossible d\'initier le paiement : '.$exception->getMessage());
            }

            if (! $transaction->redirect_url) {
                return back()->with('error', 'Djomy n\'a pas renvoyé d\'URL de paiement.');
            }

            return redirect()->away($transaction->redirect_url);
        }

        $reference = strtoupper($method).'-SUB-'.strtoupper(\Illuminate\Support\Str::random(8));
        $this->subscriptions->activate($user, $product, $method, $reference);

        return redirect()
            ->route('account', ['tab' => 'billing'])
            ->with('account_success', 'Abonnement « '.$product->name.' » activé. Profitez de vos avantages abonné.');
    }
}
