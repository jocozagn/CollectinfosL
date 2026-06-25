<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ __('site.meta_description') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4c87a7">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Collectinfos">
    <title>@yield('title', 'Collectinfos – '.__('site.tagline'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@400;700&family=Poppins:wght@700;800&family=Work+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/collectinfos.css') }}">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon-32.png') }}" type="image/png" sizes="32x32">
    <link rel="icon" href="{{ asset('favicon-16.png') }}" type="image/png" sizes="16x16">
    <link rel="apple-touch-icon" href="{{ asset('favicon-180.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">

    @stack('styles')
</head>
<body class="has-mobile-tabbar">
    <header class="site-header" id="site-header">
        <div class="header-main">
            <div class="container">
                <div class="header-row">
                    <div class="header-logo">
                        <div class="logo">
                            <a href="{{ route('home') }}">
                                <img src="{{ asset('images/collectinfo-logo.jpg') }}" alt="Collectinfos">
                            </a>
                        </div>
                        <h1 class="site-title"><a href="{{ route('home') }}">Collectinfos</a></h1>
                        <p class="site-description">{{ __('site.tagline') }}</p>
                    </div>

                    <div class="header-thematique">
                        <div class="thematique-wrap" id="thematique-wrap">
                            <button type="button" class="thematique-btn" id="thematique-btn" aria-expanded="false">
                                <i class="fa-solid fa-bars" aria-hidden="true"></i> {{ __('site.nav.themes') }}
                            </button>
                            <nav class="thematique-menu" aria-label="Thématiques">
                                @foreach ($navCategories ?? [] as $navCategory)
                                    <a href="{{ $navCategory['url'] }}">{{ strtoupper($navCategory['name']) }}</a>
                                @endforeach
                                <a href="{{ route('collaboration') }}">{{ __('site.nav.collaboration') }}</a>
                            </nav>
                        </div>
                    </div>

                    <div class="header-extras">
                        <form class="search-form" action="{{ route('contents.index') }}" method="get">
                            <input type="text" name="q" placeholder="{{ __('site.nav.search_placeholder') }}" aria-label="{{ __('site.nav.search') }}">
                            <button type="submit"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><span>{{ __('site.nav.search') }}</span></button>
                        </form>

                        <ul class="extras-menu">
                            <li class="extra-item lang-switcher">
                                <form method="POST" class="lang-switcher-form" data-locale-base="{{ url('/locale') }}">
                                    @csrf
                                    <label class="visually-hidden" for="lang-select">{{ __('site.nav.language') }}</label>
                                    <i class="fa-solid fa-globe lang-icon" aria-hidden="true"></i>
                                    <select id="lang-select" class="lang-select" aria-label="{{ __('site.nav.language') }}">
                                        @foreach (config('locales.supported', ['fr']) as $locale)
                                            <option value="{{ $locale }}" @selected(app()->getLocale() === $locale)>
                                                {{ strtoupper($locale) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </li>
                            <li class="extra-item hotline">
                                <span class="icon" aria-hidden="true"><i class="fa-solid fa-phone"></i></span>
                                <span class="hotline">
                                    <label>{{ __('site.nav.contact_us') }}</label>
                                    <span>{{ $siteContact['phone'] ?? config('collectinfos.contact.phone') }}</span>
                                </span>
                            </li>
                            <li class="extra-item">
                                <a href="{{ route('cart') }}" aria-label="{{ __('site.nav.cart') }}">
                                    <span class="icon" aria-hidden="true"><i class="fa-solid fa-cart-shopping"></i></span>
                                    @if (($cartCount ?? 0) > 0)
                                        <span class="cart-badge">{{ $cartCount }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="extra-item">
                                <a href="{{ route('account') }}" aria-label="{{ __('site.nav.account') }}">
                                    <span class="icon" aria-hidden="true"><i class="fa-solid fa-user"></i></span>
                                </a>
                            </li>
                        </ul>

                        <button type="button" class="mobile-toggle" id="mobile-toggle" aria-label="{{ __('site.nav.menu') }}" aria-expanded="false">
                            <i class="fa-solid fa-bars" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <nav class="main-nav" aria-label="Navigation principale">
            <div class="container">
                <ul>
                    <li><a href="{{ route('home') }}" @class(['active' => request()->routeIs('home')])>{{ __('site.nav.home') }}</a></li>
                    <li><a href="{{ route('contents.index') }}" @class(['active' => request()->routeIs('contents.*')])>{{ __('site.nav.contents') }}</a></li>
                    <li><a href="{{ route('products') }}" @class(['active' => request()->routeIs('products')])>{{ __('site.nav.products') }}</a></li>
                    <li><a href="{{ route('press') }}" @class(['active' => request()->routeIs('press')])>{{ __('site.nav.press') }}</a></li>
                    <li><a href="{{ route('fact-checking') }}" @class(['active' => request()->routeIs('fact-checking')])>{{ __('site.nav.fact_checking') }}</a></li>
                    <li><a href="{{ route('contact') }}" @class(['active' => request()->routeIs('contact*')])>{{ __('site.nav.contact') }}</a></li>
                    <li><a href="{{ route('collaboration') }}" @class(['active' => request()->routeIs('collaboration*')])>{{ __('site.nav.collaboration') }}</a></li>
                </ul>
            </div>
        </nav>

        <nav class="mobile-nav" id="mobile-nav" aria-label="Menu mobile">
            <a href="{{ route('home') }}">{{ __('site.nav.home') }}</a>
            <a href="{{ route('contents.index') }}">{{ __('site.nav.contents') }}</a>
            <a href="{{ route('products') }}">{{ __('site.nav.products') }}</a>
            <a href="{{ route('press') }}">{{ __('site.nav.press') }}</a>
            <a href="{{ route('fact-checking') }}">{{ __('site.nav.fact_checking') }}</a>
            <a href="{{ route('contact') }}">{{ __('site.nav.contact') }}</a>
            <a href="{{ route('collaboration') }}">{{ __('site.nav.collaboration') }}</a>
            @foreach ($navCategories ?? [] as $navCategory)
                <a href="{{ $navCategory['url'] }}">{{ strtoupper($navCategory['name']) }}</a>
            @endforeach
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-bottom">
                <div>{{ __('site.common.copyright') }} &copy; {{ date('Y') }} Collectinfos</div>
            </div>
        </div>
    </footer>

    <nav class="mobile-tabbar" aria-label="{{ __('site.nav.mobile_tabbar') }}">
        <a href="{{ route('home') }}" @class(['mobile-tabbar__item', 'is-active' => request()->routeIs('home')])>
            <i class="fa-solid fa-house" aria-hidden="true"></i>
            <span>{{ __('site.nav.tab_home') }}</span>
        </a>
        <a href="{{ route('contents.index') }}" @class(['mobile-tabbar__item', 'is-active' => request()->routeIs('contents.*')])>
            <i class="fa-solid fa-compass" aria-hidden="true"></i>
            <span>{{ __('site.nav.tab_explore') }}</span>
        </a>
        <a href="{{ route('submit-content.create') }}" @class(['mobile-tabbar__item', 'mobile-tabbar__item--action', 'is-active' => request()->routeIs('submit-content.*')]) aria-label="Publier un contenu">
            <i class="fa-solid fa-plus" aria-hidden="true"></i>
        </a>
        <a href="{{ route('contents.index') }}#catalog-search" @class(['mobile-tabbar__item', 'is-active' => request()->routeIs('contents.index') && request()->has('q')])>
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <span>{{ __('site.nav.tab_search') }}</span>
        </a>
        <a href="{{ route('account') }}" @class(['mobile-tabbar__item', 'is-active' => request()->routeIs('account*')])>
            <i class="fa-solid fa-user" aria-hidden="true"></i>
            <span>{{ __('site.nav.tab_profile') }}</span>
        </a>
    </nav>

    <button type="button" class="scroll-top" id="scroll-top" aria-label="Retour en haut">
        <i class="fa-solid fa-chevron-up" aria-hidden="true"></i>
    </button>

    <div class="preview-modal" id="preview-modal" hidden role="dialog" aria-modal="true" aria-labelledby="preview-modal-title">
        <div class="preview-modal-backdrop" data-preview-close></div>
        <div class="preview-modal-dialog">
            <div class="preview-modal-header">
                <h2 id="preview-modal-title" class="preview-modal-title">Aperçu</h2>
                <div class="preview-timer">
                    <i class="fa-regular fa-clock" aria-hidden="true"></i>
                    <span class="preview-countdown">15 s</span>
                </div>
                <button type="button" class="preview-close" aria-label="Fermer l'aperçu">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
            <div class="preview-body"></div>
            <div class="preview-progress"><span class="preview-progress-bar"></span></div>
            <p class="preview-notice">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                Aperçu limité — connectez-vous pour accéder au contenu complet.
            </p>
        </div>
    </div>

    <script>
        window.CollectinfosFavorites = {
            authenticated: @json(auth()->check()),
            slugs: @json($userFavoriteSlugs ?? []),
            toggleUrlTemplate: @json(route('favorites.toggle', ['content' => '__SLUG__'])),
            syncUrl: @json(route('favorites.sync')),
            storageKey: 'collectinfos_favorites',
        };
    </script>
    <script src="{{ asset('js/collectinfos.js') }}"></script>
    <script src="{{ asset('js/pwa.js') }}" defer></script>
    @stack('scripts')
</body>
</html>
