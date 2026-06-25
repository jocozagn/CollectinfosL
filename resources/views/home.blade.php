@extends('layouts.app')

@section('content')
    {{-- Hero --}}
    <section class="hero">
        <div class="hero-bg" role="img" aria-label="Journalistes africains"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h2 class="hero-title">{{ __('site.hero.title') }}</h2>
            <p class="hero-slogan">
                "{{ __('site.hero.slogan_line1') }}<br>
                {{ __('site.hero.slogan_line2') }}"
            </p>
            <div class="hero-buttons">
                <a href="{{ route('home') }}" class="btn-hero"><i class="fa-solid fa-cart-shopping" aria-hidden="true"></i> {{ __('site.hero.buy') }}</a>
                <a href="{{ route('submit-content.create') }}" class="btn-hero"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i> {{ __('site.hero.propose') }}</a>
                <a href="{{ route('order-content.create') }}" class="btn-hero"><i class="fa-solid fa-box" aria-hidden="true"></i> {{ __('site.hero.order') }}</a>
            </div>
        </div>
    </section>

    @if (! empty($videoExclusives))
    <section class="section-video-exclusive">
        <div class="container">
            <div class="section-title on-blue">
                <h4>EXCLUSIVITÉS VIDÉO</h4>
            </div>
            <div class="video-exclusive-grid">
                @foreach ($videoExclusives as $item)
                    <article class="video-exclusive-card">
                        @include('partials.content-card', ['item' => $item])
                    </article>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- Flux d'actualités --}}
    <section class="section-flux" id="flux">
        <div class="container">
            <div class="section-title">
                <h4>FLUX D'INFORMATIONS</h4>
            </div>
            <nav class="flux-filters" aria-label="Filtrer le flux">
                @foreach ($fluxCategories as $key => $label)
                    <a href="{{ route('home', ['flux' => $key]) }}#flux" @class(['flux-filter', 'is-active' => $fluxFilter === $key])>{{ $label }}</a>
                @endforeach
            </nav>
            <div class="products-grid">
                @foreach ($fluxItems as $item)
                    @include('partials.content-card', ['item' => $item])
                @endforeach
            </div>
        </div>
    </section>

    {{-- Sections d'infos --}}
    <section class="section-categories">
        <div class="container">
            <div class="section-title on-blue">
                <h4>{{ __('site.sections.categories') }}</h4>
            </div>
            <div class="categories-grid">
                @foreach ($categories as $category)
                    <a href="{{ $category['url'] }}" class="category-card">
                        <img src="{{ $category['image'] }}" alt="{{ $category['name'] }}" loading="lazy">
                        <div class="label">
                            <h2>{{ $category['name'] }}</h2>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Derniers reportages --}}
    <section class="section-reportages">
        <div class="container">
            <div class="section-title">
                <h4>{{ __('site.sections.reportages') }}</h4>
            </div>
            <div class="products-grid">
                @foreach ($reportages as $item)
                    @include('partials.content-card', ['item' => $item])
                @endforeach
            </div>
        </div>
    </section>

    {{-- Chiffres --}}
    <section class="section-stats">
        <div class="container">
            <div class="section-title on-blue">
                <h4>{{ __('site.sections.stats') }}</h4>
            </div>
            <div class="stats-grid">
                @foreach ($stats as $stat)
                    <div class="stat-item">
                        <div class="counter" data-target="{{ $stat['value'] }}">0</div>
                        <div class="title">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Partenaires --}}
    <section class="section-partners">
        <div class="container">
            <div class="section-title">
                <h4>{{ __('site.sections.partners') }}</h4>
            </div>
        </div>
        <div class="partners-carousel">
            <div class="partners-track">
                @foreach (array_merge($partners, $partners) as $logo)
                    <div class="partner-item">
                        <img src="{{ $logo }}" alt="Média partenaire" loading="lazy">
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Newsletter --}}
    <section class="section-newsletter" id="newsletter">
        <div class="container">
            <div class="section-title on-blue">
                <h4>{{ __('site.sections.newsletter') }}</h4>
            </div>
            <div class="newsletter-inner">
                <p class="newsletter-desc">
                    <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                    {{ __('site.sections.newsletter_desc') }}
                </p>

                @if (session('newsletter_success'))
                    <div class="newsletter-alert newsletter-alert--success" role="status">
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                        {{ session('newsletter_success') }}
                    </div>
                @endif

                @if (session('newsletter_info'))
                    <div class="newsletter-alert newsletter-alert--info" role="status">
                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                        {{ session('newsletter_info') }}
                    </div>
                @endif

                @if ($errors->has('email') || $errors->has('name'))
                    <div class="newsletter-alert newsletter-alert--error" role="alert">
                        <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                        {{ $errors->first('email') ?: $errors->first('name') }}
                    </div>
                @endif

                <form class="newsletter-form" method="POST" action="{{ route('newsletter.subscribe') }}">
                    @csrf
                    <div class="newsletter-field">
                        <label for="newsletter-name" class="sr-only">Votre nom</label>
                        <span class="newsletter-input-icon" aria-hidden="true"><i class="fa-solid fa-user"></i></span>
                        <input
                            type="text"
                            id="newsletter-name"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Votre nom (optionnel)"
                            autocomplete="name"
                        >
                    </div>
                    <div class="newsletter-field">
                        <label for="newsletter-email" class="sr-only">Votre e-mail</label>
                        <span class="newsletter-input-icon" aria-hidden="true"><i class="fa-solid fa-envelope"></i></span>
                        <input
                            type="email"
                            id="newsletter-email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="Votre adresse e-mail"
                            required
                            autocomplete="email"
                        >
                    </div>
                    <button type="submit" class="newsletter-submit">
                        <i class="fa-solid fa-paper-plane" aria-hidden="true"></i>
                        <span>S'abonner</span>
                    </button>
                </form>
                <p class="newsletter-privacy">
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                    Pas de spam. Désinscription possible à tout moment.
                </p>
            </div>
        </div>
    </section>

    {{-- Application Play Store --}}
    <section class="section-app">
        <div class="container">
            <div class="section-title">
                <h4>{{ __('site.sections.app') }}</h4>
            </div>
            <div class="app-content">
                <a
                    href="{{ config('collectinfos.app.play_store_url') }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="app-playstore-link"
                >
                    <img
                        src="{{ asset(config('collectinfos.app.play_store_badge')) }}"
                        alt="Télécharger Collectinfos sur Google Play"
                        width="280"
                        height="83"
                        loading="lazy"
                    >
                </a>
                <div class="contact-info">
                    <p class="contact-line">
                        <i class="fa-solid fa-phone" aria-hidden="true"></i>
                        <span>Tél : <strong>{{ $contact['phone'] }}</strong></span>
                    </p>
                    <p class="contact-line">
                        <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                        <span>Email : <a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></span>
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection
