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
                                <div class="cart-item-price">{{ number_format($item->price, 0) }} €</div>
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
                        <div class="cart-summary-row">
                            <span>{{ $items->count() }} article(s)</span>
                            <strong>{{ number_format($total, 0) }} €</strong>
                        </div>
                        <p class="cart-note"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> Paiement simulé — aucun frais réel ne sera prélevé.</p>

                        @auth
                            <form action="{{ route('cart.checkout') }}" method="POST">
                                @csrf
                                <button type="submit" class="ci-btn ci-btn--primary ci-btn--block">
                                    <i class="fa-solid fa-lock" aria-hidden="true"></i> Confirmer l'achat
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
