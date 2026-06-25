@extends('layouts.app')

@section('title', 'Abonnement – '.$product->name)

@section('content')
    <section class="page-hero page-hero--compact">
        <div class="container">
            <h1 class="page-title">{{ $product->name }}</h1>
            <p class="page-subtitle">{{ $product->description }}</p>
        </div>
    </section>

    <section class="section-page">
        <div class="container">
            @if (session('error'))
                <div class="ci-alert ci-alert--error">{{ session('error') }}</div>
            @endif

            @if ($activeSubscription)
                <div class="ci-alert ci-alert--success">
                    Vous avez déjà un abonnement actif jusqu'au {{ $activeSubscription->ends_at->format('d/m/Y') }}.
                    <a href="{{ route('account', ['tab' => 'billing']) }}">Voir ma facturation</a>
                </div>
            @endif

            <div class="cart-layout">
                <div class="cart-items">
                    <article class="offer-card offer-card--checkout">
                        <div class="offer-icon"><i class="fa-solid {{ $product->icon }}" aria-hidden="true"></i></div>
                        <h2>{{ $product->name }}</h2>
                        <p>{{ $product->description }}</p>
                        <ul class="subscription-perks">
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Accès aux contenus réservés aux abonnés</li>
                            @if ($product->discount_percent > 0)
                                <li><i class="fa-solid fa-check" aria-hidden="true"></i> {{ $product->discount_percent }} % de réduction sur les contenus payants</li>
                            @endif
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> Formule {{ strtolower($product->billingLabel()) }}</li>
                        </ul>
                    </article>
                </div>

                <aside class="cart-summary">
                    <h2>Récapitulatif</h2>
                    <div class="cart-summary-row cart-summary-row--total">
                        <span>{{ $product->billingLabel() }}</span>
                        <div class="cart-summary-total">
                            @include('partials.price', [
                                'amount' => (float) $product->price_eur,
                                'gnfAmount' => $product->gnfAmount(),
                                'layout' => 'stack',
                            ])
                        </div>
                    </div>

                    @auth
                        <form action="{{ route('subscriptions.checkout', $product) }}" method="POST" class="checkout-form" id="subscription-checkout-form">
                            @csrf

                            <div class="checkout-section">
                                <fieldset class="payment-methods">
                                    <legend class="checkout-section__title">
                                        <i class="fa-solid fa-wallet" aria-hidden="true"></i>
                                        Mode de paiement
                                    </legend>
                                    <div class="payment-methods__list">
                                        @foreach ($paymentMethods as $key => $method)
                                            @php
                                                $comingSoon = $method['coming_soon'] ?? false;
                                            @endphp
                                            <label @class([
                                                'payment-method-option',
                                                'payment-method-option--'.$key,
                                                'payment-method-option--coming-soon' => $comingSoon,
                                            ]) data-djomy="{{ in_array($key, $djomyMethodKeys ?? [], true) ? '1' : '0' }}">
                                                <input
                                                    type="radio"
                                                    class="payment-method-option__input"
                                                    name="payment_method"
                                                    value="{{ $key }}"
                                                    @disabled($comingSoon)
                                                    @checked(!$comingSoon && old('payment_method', $defaultPaymentMethod ?? null) === $key)
                                                >
                                                <span class="payment-method-option__card">
                                                    <span class="payment-method-option__icon" aria-hidden="true">
                                                        @if (! empty($method['logo']))
                                                            <img src="{{ asset($method['logo']) }}" alt="" class="payment-method-option__logo" loading="lazy">
                                                        @else
                                                            <i class="{{ $method['icon'] }}"></i>
                                                        @endif
                                                    </span>
                                                    <span class="payment-method-option__text">
                                                        <span class="payment-method-option__label">{{ $method['label'] }}</span>
                                                        @if ($comingSoon)
                                                            <span class="payment-method-option__soon">Bientôt disponible</span>
                                                        @endif
                                                    </span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </fieldset>

                                <div class="form-group checkout-phone" id="checkout-phone-field" hidden>
                                    <label for="payer_phone">Numéro mobile money</label>
                                    <input type="text" id="payer_phone" name="payer_phone" value="{{ old('payer_phone', auth()->user()->phone) }}" placeholder="ex: 626 89 18 27">
                                    @error('payer_phone')<span class="form-error">{{ $message }}</span>@enderror
                                </div>
                            </div>

                            <button type="submit" class="ci-btn ci-btn--primary ci-btn--block" @disabled($activeSubscription)>
                                <i class="fa-solid fa-credit-card" aria-hidden="true"></i> Souscrire maintenant
                            </button>
                        </form>
                    @else
                        <p class="form-hint">Connectez-vous pour souscrire.</p>
                        <a href="{{ route('account') }}" class="ci-btn ci-btn--primary ci-btn--block">Se connecter</a>
                    @endauth

                    <p class="cart-conversion-note">
                        <i class="fa-solid fa-coins" aria-hidden="true"></i>
                        Paiement Djomy en francs guinéens.
                    </p>
                </aside>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('subscription-checkout-form');
            if (!form) return;

            const phoneField = document.getElementById('checkout-phone-field');
            const togglePhone = () => {
                const selected = form.querySelector('input[name="payment_method"]:checked');
                const show = selected && selected.closest('[data-djomy="1"]');
                if (phoneField) phoneField.hidden = !show;
            };

            form.querySelectorAll('input[name="payment_method"]').forEach((input) => {
                input.addEventListener('change', togglePhone);
            });
            togglePhone();
        });
    </script>
@endpush
