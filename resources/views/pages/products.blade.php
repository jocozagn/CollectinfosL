@extends('layouts.app')

@section('title', 'Nos produits – Collectinfos')

@section('content')
    <section class="page-hero page-hero--compact">
        <div class="container">
            <h1 class="page-title">Nos produits</h1>
            <p class="page-subtitle">Abonnements, licences et packs de contenus pour médias et professionnels.</p>
        </div>
    </section>

    <section class="section-page">
        <div class="container">
            <div class="products-offer-grid">
                @foreach ($products as $product)
                    <article class="offer-card">
                        <div class="offer-icon"><i class="fa-solid {{ $product['icon'] }}" aria-hidden="true"></i></div>
                        <h3>{{ $product['name'] }}</h3>
                        <p>{{ $product['description'] }}</p>
                        <div class="offer-price">{{ $product['price'] }}</div>
                        <a href="{{ route('contact') }}" class="ci-btn ci-btn--outline">Nous contacter</a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endsection
