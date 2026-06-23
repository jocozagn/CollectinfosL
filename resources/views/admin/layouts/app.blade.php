<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Administration') – Collectinfos</title>
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="admin-body">
    <div class="admin-sidebar-overlay" id="admin-sidebar-overlay" aria-hidden="true"></div>

    <div class="admin-wrapper">
        <aside class="admin-sidebar" id="admin-sidebar">
            <div class="admin-brand">
                <a href="{{ route('admin.dashboard') }}">
                    <strong>Collectinfos</strong>
                    <span>Administration</span>
                </a>
            </div>
            <nav class="admin-nav">
                <a href="{{ route('admin.dashboard') }}" @class(['active' => request()->routeIs('admin.dashboard')])>
                    <i class="fa-solid fa-gauge-high" aria-hidden="true"></i> Tableau de bord
                </a>
                <a href="{{ route('admin.contents.index') }}" @class(['active' => request()->routeIs('admin.contents.index', 'admin.contents.edit')])>
                    <i class="fa-solid fa-folder-open" aria-hidden="true"></i> Contenus
                </a>
                <a href="{{ route('admin.contents.create') }}" @class(['active' => request()->routeIs('admin.contents.create')])>
                    <i class="fa-solid fa-circle-plus" aria-hidden="true"></i> Nouveau contenu
                </a>
                <span class="admin-nav-label">Référentiels</span>
                <a href="{{ route('admin.taxonomies.index', 'categories') }}" @class(['active' => request()->routeIs('admin.taxonomies.*') && request()->route('kind') === 'categories'])>
                    <i class="fa-solid fa-folder-tree" aria-hidden="true"></i> Catégories
                </a>
                <a href="{{ route('admin.taxonomies.index', 'themes') }}" @class(['active' => request()->routeIs('admin.taxonomies.*') && request()->route('kind') === 'themes'])>
                    <i class="fa-solid fa-tags" aria-hidden="true"></i> Thèmes
                </a>
                <a href="{{ route('admin.taxonomies.index', 'types') }}" @class(['active' => request()->routeIs('admin.taxonomies.*') && request()->route('kind') === 'types'])>
                    <i class="fa-solid fa-shapes" aria-hidden="true"></i> Types de contenu
                </a>
                <a href="{{ route('admin.site-stats.index') }}" @class(['active' => request()->routeIs('admin.site-stats.*')])>
                    <i class="fa-solid fa-chart-simple" aria-hidden="true"></i> Statistiques accueil
                </a>
                <a href="{{ route('admin.partners.index') }}" @class(['active' => request()->routeIs('admin.partners.*')])>
                    <i class="fa-solid fa-handshake-angle" aria-hidden="true"></i> Partenaires médias
                </a>
                <a href="{{ route('admin.products.index') }}" @class(['active' => request()->routeIs('admin.products.*')])>
                    <i class="fa-solid fa-box-open" aria-hidden="true"></i> Nos produits
                </a>
                <a href="{{ route('admin.settings.contact') }}" @class(['active' => request()->routeIs('admin.settings.contact')])>
                    <i class="fa-solid fa-address-card" aria-hidden="true"></i> Coordonnées
                </a>
                <a href="{{ route('admin.settings.press') }}" @class(['active' => request()->routeIs('admin.settings.press')])>
                    <i class="fa-solid fa-bullhorn" aria-hidden="true"></i> Relations presse
                </a>
                <a href="{{ route('admin.settings.fact-checking') }}" @class(['active' => request()->routeIs('admin.settings.fact-checking')])>
                    <i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Fact-checking
                </a>
                <span class="admin-nav-label">Communication</span>
                <a href="{{ route('admin.messages.index') }}" @class(['active' => request()->routeIs('admin.messages.*')])>
                    <i class="fa-solid fa-envelope" aria-hidden="true"></i> Messages contact
                </a>
                <a href="{{ route('admin.collaboration.index') }}" @class(['active' => request()->routeIs('admin.collaboration.*')])>
                    <i class="fa-solid fa-handshake" aria-hidden="true"></i> Candidatures
                </a>
                <a href="{{ route('admin.newsletter.index') }}" @class(['active' => request()->routeIs('admin.newsletter.*')])>
                    <i class="fa-solid fa-newspaper" aria-hidden="true"></i> Newsletter
                </a>
                <span class="admin-nav-label">Collaboration</span>
                <a href="{{ route('admin.investigations.index') }}" @class(['active' => request()->routeIs('admin.investigations.*')])>
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Enquêtes
                </a>
            </nav>
            <form action="{{ route('admin.logout') }}" method="POST" class="admin-logout">
                @csrf
                <button type="submit"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Déconnexion</button>
            </form>
        </aside>

        <div class="admin-main-ctn">
            <header class="admin-topbar">
                <button type="button" class="admin-sidebar-toggle" id="admin-sidebar-toggle" aria-label="Menu" aria-expanded="false">
                    <i class="fa-solid fa-bars" aria-hidden="true"></i>
                </button>
                <div class="admin-topbar-end">
                    <a href="{{ route('home') }}" target="_blank" class="admin-topbar-link">
                        <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> Voir le site
                    </a>
                    <span class="admin-user">
                        <i class="fa-solid fa-user-circle" aria-hidden="true"></i>
                        {{ auth()->user()->name }}
                    </span>
                </div>
            </header>

            <main class="admin-main">
                <header class="admin-page-header">
                    <h1>@yield('page-title', 'Administration')</h1>
                </header>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-error">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="{{ asset('js/admin.js') }}"></script>
</body>
</html>
