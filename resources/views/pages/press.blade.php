@extends('layouts.app')

@section('title', 'Relations presse – Collectinfos')

@section('content')
    <section class="page-hero page-hero--compact">
        <div class="container">
            <h1 class="page-title">Relations presse</h1>
            <p class="page-subtitle">{{ $press['intro'] }}</p>
        </div>
    </section>

    <section class="section-page">
        <div class="container">
            <div class="page-grid">
                <div class="page-content">
                    <h2 class="content-subtitle">Nos services presse</h2>
                    <ul class="check-list">
                        @foreach ($press['services'] as $service)
                            <li><i class="fa-solid fa-check" aria-hidden="true"></i> {{ $service }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="page-info">
                    <div class="info-card info-card--highlight">
                        <i class="fa-solid fa-headset" aria-hidden="true"></i>
                        <h3>Contact presse</h3>
                        <p>E-mail : <a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></p>
                        <p>Tél : <strong>{{ $contact['phone'] }}</strong></p>
                        <a href="{{ route('contact') }}" class="ci-btn ci-btn--primary">Demander un dossier</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
