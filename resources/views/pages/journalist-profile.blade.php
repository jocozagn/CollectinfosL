@extends('layouts.app')

@section('title', $journalist->name.' – Collectinfos')

@section('content')
    <section class="page-hero page-hero--compact">
        <div class="container journalist-profile-hero">
            <div>
                <h1 class="page-title">{{ $journalist->name }}</h1>
                <p class="page-subtitle">
                    {{ $journalist->accountTypeLabel() }}
                    @if ($journalist->country)
                        · {{ $journalist->city ? $journalist->city.', ' : '' }}{{ $journalist->country }}
                    @endif
                </p>
                @if ($journalist->isProfileVerified())
                    <span class="badge badge-verified"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Profil vérifié</span>
                @endif
            </div>
        </div>
    </section>

    <section class="section-page">
        <div class="container journalist-profile-body">
            @if ($journalist->bio)
                <div class="profile-bio">{{ $journalist->bio }}</div>
            @endif

            <dl class="profile-facts">
                @if ($journalist->meta('specialties'))
                    <div><dt>Spécialités</dt><dd>{{ $journalist->meta('specialties') }}</dd></div>
                @endif
                @if ($journalist->meta('languages'))
                    <div><dt>Langues</dt><dd>{{ $journalist->meta('languages') }}</dd></div>
                @endif
                @if ($journalist->meta('coverage_zones'))
                    <div><dt>Zones couvertes</dt><dd>{{ $journalist->meta('coverage_zones') }}</dd></div>
                @endif
                @if ($journalist->meta('experience_years'))
                    <div><dt>Expérience</dt><dd>{{ $journalist->meta('experience_years') }} ans</dd></div>
                @endif
                @if ($journalist->meta('media_worked'))
                    <div><dt>Médias</dt><dd>{{ $journalist->meta('media_worked') }}</dd></div>
                @endif
            </dl>

            <div class="profile-links">
                @if ($journalist->meta('portfolio_url'))
                    <a href="{{ $journalist->meta('portfolio_url') }}" target="_blank" rel="noopener" class="ci-btn ci-btn--outline ci-btn--sm">
                        <i class="fa-solid fa-link" aria-hidden="true"></i> Portfolio
                    </a>
                @endif
                @if ($journalist->meta('linkedin_url'))
                    <a href="{{ $journalist->meta('linkedin_url') }}" target="_blank" rel="noopener" class="ci-btn ci-btn--outline ci-btn--sm">LinkedIn</a>
                @endif
                @if ($journalist->meta('twitter_url'))
                    <a href="{{ $journalist->meta('twitter_url') }}" target="_blank" rel="noopener" class="ci-btn ci-btn--outline ci-btn--sm">X / Twitter</a>
                @endif
            </div>

            @if ($contents->isEmpty())
                <p>Aucun contenu publié pour le moment.</p>
            @else
                <h2 class="form-heading">Publications</h2>
                <div class="products-grid">
                    @foreach ($contents as $content)
                        @include('partials.content-card', ['item' => $content->toCardArray()])
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
