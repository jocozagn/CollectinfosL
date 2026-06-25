<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Services\CartService;
use App\Services\CurrencyService;
use App\Services\Djomy\DjomyClient;
use App\Services\Djomy\DjomyPaymentService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private CartService $cart,
        private PaymentService $payments,
        private DjomyPaymentService $djomyPayments,
        private DjomyClient $djomyClient,
        private CurrencyService $currency,
    ) {}

    public function index(): View
    {
        $items = $this->cart->items();
        $commissionRate = $this->payments->commissionRate();
        $user = Auth::user();
        $total = $this->cart->total($user);
        $checkoutMethods = $this->payments->checkoutMethods();

        return view('pages.cart', [
            'items' => $items,
            'total' => $total,
            'totalGnf' => $this->currency->cartTotalGnf($items, $user),
            'platformFeeGnf' => (int) round($this->currency->cartTotalGnf($items, $user) * $commissionRate),
            'eurToGnfRate' => $this->currency->eurToGnfRate(),
            'paymentMethods' => $this->payments->methods(),
            'checkoutMethodKeys' => array_keys($checkoutMethods),
            'defaultPaymentMethod' => array_key_first($checkoutMethods),
            'djomyEnabled' => $this->djomyClient->isConfigured(),
            'djomyMethodKeys' => array_keys(config('djomy.method_map', [])),
            'commissionRate' => $commissionRate,
            'platformFee' => round($total * $commissionRate, 2),
            'currency' => $this->currency,
            'subscriptionDiscount' => $user?->subscriptionDiscountPercent() ?? 0,
        ]);
    }

    public function add(Content $content): RedirectResponse
    {
        if (! $content->isPurchasable()) {
            if ($content->isExclusive() && $content->isSoldExclusively()) {
                return back()->with('error', 'Cette exclusivité a déjà été vendue à un seul acheteur.');
            }

            return back()->with('error', 'Ce contenu ne peut pas être ajouté au panier.');
        }

        if (Auth::check() && Auth::user()->hasPurchased($content)) {
            return back()->with('error', 'Vous possédez déjà ce contenu.');
        }

        $this->cart->add($content);

        return back()->with('cart_success', '« '.$content->title.' » a été ajouté au panier.');
    }

    public function remove(Content $content): RedirectResponse
    {
        $this->cart->remove($content->id);

        return redirect()->route('cart')->with('cart_success', 'Article retiré du panier.');
    }

    public function checkout(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('account')
                ->with('error', 'Connectez-vous pour finaliser votre achat.');
        }

        $items = $this->cart->items();

        if ($items->isEmpty()) {
            return redirect()->route('cart')->with('error', 'Votre panier est vide.');
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
                $transaction = $this->djomyPayments->initiateCheckout($user, $items, $method, $phone);
            } catch (\Throwable $exception) {
                return back()->with('error', 'Impossible d\'initier le paiement Djomy : '.$exception->getMessage());
            }

            if (! $transaction->redirect_url) {
                return back()->with('error', 'Djomy n\'a pas renvoyé d\'URL de paiement. Vérifiez la configuration API.');
            }

            return redirect()->away($transaction->redirect_url);
        }

        $result = ['purchased' => 0, 'unavailable' => []];

        DB::transaction(function () use ($items, $user, $method, &$result) {
            $result = $this->payments->processCheckout($user, $items, $method);
        });

        $this->cart->clear();

        if ($result['purchased'] === 0 && $result['unavailable'] !== []) {
            return redirect()->route('cart')->with(
                'error',
                'Certains contenus ne sont plus disponibles : '.implode(', ', $result['unavailable']).'.'
            );
        }

        $message = $result['purchased'] > 0
            ? 'Paiement enregistré ('.($this->payments->methods()[$method]['label'] ?? $method).'). '
                .$result['purchased'].' contenu(s) disponibles dans votre compte.'
            : 'Tous les articles de votre panier étaient déjà achetés.';

        return redirect()->route('account', ['tab' => 'purchases'])->with('account_success', $message);
    }
}
