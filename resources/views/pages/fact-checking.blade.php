@extends('layouts.app')

@section('title', 'Fact-checking – Collectinfos')

@section('content')
    <section class="page-hero page-hero--compact">
        <div class="container">
            <h1 class="page-title">Fact-checking</h1>
            <p class="page-subtitle">{{ $factChecking['intro'] }}</p>
        </div>
    </section>

    <section class="section-page">
        <div class="container">
            <div class="fact-grid">
                @foreach ($factChecking['criteria'] as $item)
                    <article class="fact-card">
                        <div class="fact-icon"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i></div>
                        <h3>{{ $item['title'] }}</h3>
                        <p>{{ $item['text'] }}</p>
                    </article>
                @endforeach
            </div>
            <div class="page-cta">
                <p>{{ $factChecking['cta'] ?? 'Vous avez une information à vérifier ?' }}</p>
                <a href="{{ route('contact') }}" class="ci-btn ci-btn--primary">
                    <i class="fa-solid fa-envelope" aria-hidden="true"></i> Contactez notre rédaction
                </a>
            </div>
        </div>
    </section>
@endsection
