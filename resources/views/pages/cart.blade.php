@extends('layouts.app')

@section('title', 'Panier – Collectinfos')

@section('content')
    <section class="page-hero page-hero--compact">
        <div class="container">
            <h1 class="page-title">Mon panier</h1>
            <p class="page-subtitle">Votre sélection de contenus à acquérir.</p>
        </div>
    </section>

    <section class="section-page">
        <div class="container">
            @if (session('cart_success'))
                <div class="ci-alert ci-alert--success">{{ session('cart_success') }}</div>
            @endif
            @if (session('error'))
                <div class="ci-alert ci-alert--error">{{ session('error') }}</div>
            @endif

            @if ($items->isEmpty())
                <div class="empty-state-page">
                    <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>
                    <h2>Votre panier est vide</h2>
                    <p>Parcourez nos contenus et ajoutez des reportages à votre panier.</p>
                    <a href="{{ route('contents.index') }}" class="ci-btn ci-btn--primary">
                        <i class="fa-solid fa-film" aria-hidden="true"></i> Voir nos contenus
                    </a>
                </div>
            @else
                <div class="cart-layout">
                    <div class="cart-items">
                        @foreach ($items as $item)
                            <article class="cart-item">
                                @if ($item->thumbnailUrl())
                                    <img src="{{ $item->thumbnailUrl() }}" alt="" class="cart-item-thumb">
                                @endif
                                <div class="cart-item-info">
                                    <h3><a href="{{ route('contents.show', $item->slug) }}">{{ $item->title }}</a></h3>
                                    <p>{{ \App\Models\Content::typeLabels()[$item->type] ?? $item->type }}</p>
                                </div>
                                <div class="cart-item-price">
                                    @include('partials.price', ['content' => $item, 'layout' => 'stack'])
                                </div>
                                <form action="{{ route('cart.remove', $item) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="cart-item-remove" title="Retirer" aria-label="Retirer du panier">
                                        <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                    </button>
                                </form>
                            </article>
                        @endforeach
                    </div>

                    <aside class="cart-summary">
                        <h2>Récapitulatif</h2>
                        <p class="cart-summary-meta">{{ $items->count() }} article(s)</p>

                        @if (($subscriptionDiscount ?? 0) > 0)
                            <p class="cart-discount-note">
                                <i class="fa-solid fa-percent" aria-hidden="true"></i>
                                Tarif abonné : {{ $subscriptionDiscount }} % de réduction appliquée.
                            </p>
                        @endif

                        <div class="cart-summary-row cart-summary-row--total">
                            <span>Total</span>
                            <div class="cart-summary-total">
                                @include('partials.price', ['amount' => $total, 'gnfAmount' => $totalGnf, 'layout' => 'stack'])
                            </div>
                        </div>
                        <div class="cart-summary-row cart-summary-row--muted">
                            <span>Commission ({{ (int) ($commissionRate * 100) }}%)</span>
                            <div class="cart-summary-total cart-summary-total--sm">
                                @include('partials.price', ['amount' => $platformFee, 'gnfAmount' => $platformFeeGnf, 'layout' => 'stack'])
                            </div>
                        </div>

                        <p class="cart-conversion-note">
                            <i class="fa-solid fa-coins" aria-hidden="true"></i>
                            Paiement Djomy en francs guinéens.
                            <span class="cart-conversion-note__hint">Les prix GNF sont fixés à la publication ; sinon conversion indicative (1 € = {{ number_format($eurToGnfRate, 0, ',', ' ') }} GNF).</span>
                        </p>

                        @auth
                            <form action="{{ route('cart.checkout') }}" method="POST" class="checkout-form" id="checkout-form">
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
                                                    $isCheckout = in_array($key, $checkoutMethodKeys ?? [], true);
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
                                                        @if ($key === ($defaultPaymentMethod ?? null)) required @endif
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
                                                        <span class="payment-method-option__check" aria-hidden="true">
                                                            <i class="fa-solid fa-circle-check"></i>
                                                        </span>
                                                    </span>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('payment_method')
                                            <span class="form-error">{{ $message }}</span>
                                        @enderror
                                    </fieldset>
                                </div>

                                @if ($djomyEnabled ?? false)
                                    <div class="checkout-section checkout-phone-section" id="checkout-phone-section">
                                        <label class="checkout-section__title checkout-section__title--label" for="payer_phone">
                                            <i class="fa-solid fa-mobile-screen" aria-hidden="true"></i>
                                            Numéro mobile money
                                        </label>
                                        <div class="checkout-phone-field">
                                            <span class="checkout-phone-prefix" aria-hidden="true">+224</span>
                                            <input
                                                type="tel"
                                                id="payer_phone"
                                                name="payer_phone"
                                                class="checkout-phone-input"
                                                value="{{ old('payer_phone', auth()->user()->phone) }}"
                                                placeholder="620 00 00 00"
                                                inputmode="tel"
                                                autocomplete="tel"
                                            >
                                        </div>
                                        <p class="checkout-phone-hint">Requis pour Orange Money, Wave, MTN et carte via Djomy.</p>
                                        @error('payer_phone')
                                            <span class="form-error">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <p class="checkout-secure-note">
                                        <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                                        Paiement sécurisé via <strong>Djomy</strong>
                                    </p>
                                @else
                                    <p class="cart-note">
                                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                        Activez Djomy dans la configuration pour les paiements mobile money en production.
                                    </p>
                                @endif

                                <button type="submit" class="ci-btn ci-btn--primary ci-btn--block checkout-submit" id="checkout-submit">
                                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                    <span id="checkout-submit-label">
                                        Payer <span class="checkout-pay-eur">{{ $currency->formatEur($total) }}</span>
                                        <span class="checkout-pay-gnf" data-gnf="{{ $totalGnf }}">({{ $currency->formatGnf($totalGnf) }} via Djomy)</span>
                                    </span>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('account') }}" class="ci-btn ci-btn--primary ci-btn--block">
                                <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i> Se connecter pour acheter
                            </a>
                        @endauth

                        <a href="{{ route('contents.index') }}" class="ci-btn ci-btn--outline ci-btn--block">
                            Continuer mes achats
                        </a>
                    </aside>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('checkout-form');
            if (!form) return;

            var phoneSection = document.getElementById('checkout-phone-section');
            var phoneInput = document.getElementById('payer_phone');
            var payGnf = document.querySelector('.checkout-pay-gnf');
            var payEur = document.querySelector('.checkout-pay-eur');

            function syncCheckoutUi() {
                var selected = form.querySelector('input[name="payment_method"]:checked');
                var isDjomy = selected && selected.closest('.payment-method-option')?.dataset.djomy === '1';

                if (phoneSection) {
                    phoneSection.hidden = !isDjomy;
                }
                if (phoneInput) {
                    phoneInput.required = !!isDjomy && !!phoneSection;
                }
                if (payGnf && payEur) {
                    payGnf.hidden = !isDjomy;
                    payEur.hidden = isDjomy;
                }
            }

            form.querySelectorAll('input[name="payment_method"]').forEach(function (radio) {
                radio.addEventListener('change', syncCheckoutUi);
            });

            syncCheckoutUi();
        });
    </script>
@endpush
