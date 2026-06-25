@extends('layouts.app')



@section('title', 'Mon compte – Collectinfos')



@section('content')

    <section class="page-hero page-hero--compact">

        <div class="container">

            <h1 class="page-title">Bonjour, {{ $user->name }}</h1>

            <p class="page-subtitle">

                @if ($user->isJournalist())

                    Espace journaliste — gérez vos enquêtes, candidatures et achats.

                @else

                    Gérez vos achats et accédez à vos contenus.

                @endif

            </p>

        </div>

    </section>



    <section class="section-page">

        <div class="container">

            @if (session('account_success'))

                <div class="ci-alert ci-alert--success">{{ session('account_success') }}</div>

            @endif



            <div class="account-dashboard">

                <div class="account-sidebar">

                    <div class="sidebar-box">

                        <h3>Mon profil</h3>

                        @if ($user->isJournalist())

                            <p class="account-role-badge"><i class="fa-solid fa-id-card" aria-hidden="true"></i> Compte journaliste</p>

                        @endif

                        <p><i class="fa-solid fa-user" aria-hidden="true"></i> {{ $user->name }}</p>

                        <p><i class="fa-solid fa-envelope" aria-hidden="true"></i> {{ $user->email }}</p>

                        <p><i class="fa-solid fa-film" aria-hidden="true"></i> {{ $purchases->count() }} contenu(s) acheté(s)</p>

                        <p><i class="fa-solid fa-heart" aria-hidden="true"></i> {{ $favorites->count() }} favori(s)</p>

                        @if ($user->isJournalist())

                            <p><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> {{ $ownedInvestigations->count() }} enquête(s) émise(s)</p>

                        @endif

                    </div>

                    @if ($user->isJournalist())
                        <p><i class="fa-solid fa-wallet" aria-hidden="true"></i> {{ number_format($user->wallet_balance, 0) }} € disponibles</p>
                    @endif

                    <a href="{{ route('submit-content.create') }}" class="ci-btn ci-btn--primary ci-btn--block" style="margin-bottom: 12px;">
                        <i class="fa-solid fa-plus" aria-hidden="true"></i> Déposer un contenu
                    </a>
                    <a href="{{ route('order-content.create') }}" class="ci-btn ci-btn--outline ci-btn--block" style="margin-bottom: 12px;">
                        <i class="fa-solid fa-box" aria-hidden="true"></i> Commander un sujet
                    </a>
                    <a href="{{ route('collaboration') }}" class="ci-btn ci-btn--outline ci-btn--block" style="margin-bottom: 12px;">
                        <i class="fa-solid fa-handshake" aria-hidden="true"></i> Collaboration
                    </a>

                    <form action="{{ route('account.logout') }}" method="POST">

                        @csrf

                        <button type="submit" class="ci-btn ci-btn--outline ci-btn--block">

                            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Déconnexion

                        </button>

                    </form>

                </div>



                <div class="account-main">

                    <nav class="account-tabs" aria-label="Sections du compte">

                        @if ($user->isJournalist())

                            <a href="{{ route('account', ['tab' => 'investigations']) }}" @class(['account-tab', 'active' => $tab === 'investigations'])>

                                <i class="fa-solid fa-lightbulb" aria-hidden="true"></i> Mes enquêtes

                            </a>

                            <a href="{{ route('account', ['tab' => 'applications']) }}" @class(['account-tab', 'active' => $tab === 'applications'])>

                                <i class="fa-solid fa-inbox" aria-hidden="true"></i> Mes candidatures

                            </a>

                            <a href="{{ route('account', ['tab' => 'participations']) }}" @class(['account-tab', 'active' => $tab === 'participations'])>

                                <i class="fa-solid fa-users" aria-hidden="true"></i> Enquêtes rejointes

                            </a>

                            <a href="{{ route('account', ['tab' => 'publications']) }}" @class(['account-tab', 'active' => $tab === 'publications'])>

                                <i class="fa-solid fa-newspaper" aria-hidden="true"></i> Publications

                            </a>

                            <a href="{{ route('account', ['tab' => 'sales']) }}" @class(['account-tab', 'active' => $tab === 'sales'])>

                                <i class="fa-solid fa-chart-line" aria-hidden="true"></i> Ventes

                            </a>

                            <a href="{{ route('account', ['tab' => 'wallet']) }}" @class(['account-tab', 'active' => $tab === 'wallet'])>

                                <i class="fa-solid fa-wallet" aria-hidden="true"></i> Portefeuille

                            </a>

                            <a href="{{ route('account', ['tab' => 'received_orders']) }}" @class(['account-tab', 'active' => $tab === 'received_orders'])>

                                <i class="fa-solid fa-inbox" aria-hidden="true"></i> Commandes reçues

                            </a>

                        @endif

                        <a href="{{ route('account', ['tab' => 'orders']) }}" @class(['account-tab', 'active' => $tab === 'orders'])>

                            <i class="fa-solid fa-box" aria-hidden="true"></i> Mes commandes

                        </a>

                        <a href="{{ route('account', ['tab' => 'favorites']) }}" @class(['account-tab', 'active' => $tab === 'favorites'])>

                            <i class="fa-solid fa-heart" aria-hidden="true"></i> Mes favoris

                        </a>

                        <a href="{{ route('account', ['tab' => 'purchases']) }}" @class(['account-tab', 'active' => $tab === 'purchases'])>

                            <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i> Mes achats

                        </a>

                        <a href="{{ route('account', ['tab' => 'billing']) }}" @class(['account-tab', 'active' => $tab === 'billing'])>

                            <i class="fa-solid fa-file-invoice" aria-hidden="true"></i> Facturation

                        </a>

                        <a href="{{ route('account', ['tab' => 'notifications']) }}" @class(['account-tab', 'active' => $tab === 'notifications'])>

                            <i class="fa-solid fa-bell" aria-hidden="true"></i> Notifications

                            @if (($unreadNotifications ?? 0) > 0)

                                <span class="tab-badge">{{ $unreadNotifications }}</span>

                            @endif

                        </a>

                        <a href="{{ route('account', ['tab' => 'profile']) }}" @class(['account-tab', 'active' => $tab === 'profile'])>

                            <i class="fa-solid fa-id-card" aria-hidden="true"></i> Profil

                        </a>

                    </nav>



                    @if ($user->isJournalist() && $tab === 'investigations')

                        <div class="account-panel">

                            <h2 class="form-heading"><i class="fa-solid fa-lightbulb" aria-hidden="true"></i> Mes enquêtes émises</h2>



                            @if ($ownedInvestigations->isEmpty())

                                <p class="form-intro">Vous n'avez pas encore proposé d'enquête.</p>

                            @else

                                <div class="investigation-account-list">

                                    @foreach ($ownedInvestigations as $investigation)

                                        <article class="investigation-account-card">

                                            <div class="investigation-account-head">

                                                <h3>{{ $investigation->title }}</h3>

                                                <span @class([

                                                    'status-pill',

                                                    'status-pill--open' => $investigation->status === 'open',

                                                    'status-pill--closed' => $investigation->status === 'closed',

                                                    'status-pill--pending' => $investigation->status === 'pending',

                                                ])>{{ $investigation->statusLabel() }}</span>

                                            </div>

                                            <p>{{ Str::limit($investigation->summary, 160) }}</p>

                                            <ul class="investigation-account-meta">

                                                @if ($investigation->country)

                                                    <li><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $investigation->country }}</li>

                                                @endif

                                                @if ($investigation->themeLabel())

                                                    <li><i class="fa-solid fa-tag" aria-hidden="true"></i> {{ $investigation->themeLabel() }}</li>

                                                @endif

                                                <li><i class="fa-solid fa-user-group" aria-hidden="true"></i> {{ $investigation->places }} place(s)</li>

                                                <li><i class="fa-solid fa-calendar" aria-hidden="true"></i> {{ $investigation->created_at->format('d/m/Y') }}</li>

                                            </ul>

                                            <div class="investigation-account-actions">
                                                <a href="{{ route('account.investigations.show', $investigation) }}" class="ci-btn ci-btn--primary ci-btn--sm">
                                                    <i class="fa-solid fa-door-open" aria-hidden="true"></i> Ouvrir l'espace collaboratif
                                                </a>
                                                @if (($investigation->pending_candidatures_count ?? 0) > 0)
                                                    <a href="{{ route('account.investigations.show', ['investigation' => $investigation->slug, 'section' => 'candidatures']) }}" class="ci-btn ci-btn--outline ci-btn--sm">
                                                        <i class="fa-solid fa-user-plus" aria-hidden="true"></i>
                                                        {{ $investigation->pending_candidatures_count }} candidature(s)
                                                    </a>
                                                @endif
                                            </div>

                                        </article>

                                    @endforeach

                                </div>

                            @endif



                            <div class="page-form-wrap" style="margin-top: 28px;">

                                <h3 class="form-heading"><i class="fa-solid fa-circle-plus" aria-hidden="true"></i> Proposer une nouvelle enquête</h3>

                                <form class="ci-form" method="POST" action="{{ route('account.investigations.store') }}">

                                    @csrf

                                    <div class="form-group">

                                        <label for="inv-title">Titre de l'enquête *</label>

                                        <input type="text" id="inv-title" name="title" value="{{ old('title') }}" required>

                                        @error('title')<span class="field-error">{{ $message }}</span>@enderror

                                    </div>

                                    <div class="form-group">

                                        <label for="inv-summary">Résumé / angle éditorial *</label>

                                        <textarea id="inv-summary" name="summary" rows="4" required>{{ old('summary') }}</textarea>

                                        @error('summary')<span class="field-error">{{ $message }}</span>@enderror

                                    </div>

                                    <div class="form-row">

                                        <div class="form-group">

                                            <label for="inv-country">Pays / zone</label>

                                            <input type="text" id="inv-country" name="country" value="{{ old('country') }}">

                                        </div>

                                        <div class="form-group">

                                            <label for="inv-theme">Thème</label>

                                            <select id="inv-theme" name="theme">

                                                <option value="">— Choisir —</option>

                                                @foreach ($themes as $key => $label)

                                                    <option value="{{ $key }}" @selected(old('theme') === $key)>{{ $label }}</option>

                                                @endforeach

                                            </select>

                                        </div>

                                    </div>

                                    <div class="form-group">

                                        <label for="inv-places">Places pour journalistes *</label>

                                        <input type="number" id="inv-places" name="places" min="1" max="50" value="{{ old('places', 3) }}" required>

                                    </div>

                                    <button type="submit" class="ci-btn ci-btn--primary">

                                        <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Soumettre pour validation

                                    </button>

                                </form>

                            </div>

                        </div>

                    @elseif ($user->isJournalist() && $tab === 'applications')

                        <div class="account-panel">

                            <h2 class="form-heading"><i class="fa-solid fa-inbox" aria-hidden="true"></i> Mes candidatures</h2>



                            @if ($applications->isEmpty())

                                <div class="empty-state-page empty-state-page--inline">

                                    <p>Aucune candidature enregistrée.</p>

                                    <a href="{{ route('collaboration') }}" class="ci-btn ci-btn--primary">Rejoindre ou proposer une enquête</a>

                                </div>

                            @else

                                <div class="investigation-account-list">

                                    @foreach ($applications as $application)

                                        <article class="investigation-account-card">

                                            <div class="investigation-account-head">

                                                <h3>

                                                    @if ($application->type === 'propose')

                                                        {{ $application->proposed_title ?? 'Proposition d\'enquête' }}

                                                    @else

                                                        {{ $application->investigation?->title ?? 'Candidature enquête' }}

                                                    @endif

                                                </h3>

                                                <span @class([

                                                    'status-pill',

                                                    'status-pill--open' => $application->status === 'accepted',

                                                    'status-pill--closed' => $application->status === 'rejected',

                                                    'status-pill--pending' => $application->status === 'pending',

                                                ])>{{ $application->statusLabel() }}</span>

                                            </div>

                                            <p><strong>{{ $application->typeLabel() }}</strong> — {{ Str::limit($application->message, 140) }}</p>

                                            <ul class="investigation-account-meta">

                                                <li><i class="fa-solid fa-calendar" aria-hidden="true"></i> {{ $application->created_at->format('d/m/Y') }}</li>

                                                @if ($application->country)

                                                    <li><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $application->country }}</li>

                                                @endif

                                            </ul>

                                            @if ($application->status === 'accepted' && $application->investigation)
                                                <div class="investigation-account-actions">
                                                    <a href="{{ route('account.investigations.show', $application->investigation) }}" class="ci-btn ci-btn--primary ci-btn--sm">
                                                        <i class="fa-solid fa-door-open" aria-hidden="true"></i> Ouvrir l'espace collaboratif
                                                    </a>
                                                </div>
                                            @endif

                                        </article>

                                    @endforeach

                                </div>

                            @endif

                        </div>

                    @elseif ($user->isJournalist() && $tab === 'participations')

                        <div class="account-panel">

                            <h2 class="form-heading"><i class="fa-solid fa-users" aria-hidden="true"></i> Enquêtes auxquelles je participe</h2>



                            @if ($participations->isEmpty())

                                <div class="empty-state-page empty-state-page--inline">

                                    <p>Vous ne participez encore à aucune enquête d'un autre porteur.</p>

                                    <a href="{{ route('collaboration') }}" class="ci-btn ci-btn--primary">Voir les enquêtes ouvertes</a>

                                </div>

                            @else

                                <div class="investigation-account-list">

                                    @foreach ($participations as $investigation)

                                        <article class="investigation-account-card">

                                            <div class="investigation-account-head">

                                                <h3>{{ $investigation->title }}</h3>

                                                <span @class([

                                                    'status-pill',

                                                    'status-pill--open' => $investigation->status === 'open',

                                                    'status-pill--closed' => $investigation->status === 'closed',

                                                    'status-pill--pending' => $investigation->status === 'pending',

                                                ])>{{ $investigation->statusLabel() }}</span>

                                            </div>

                                            <p>{{ Str::limit($investigation->summary, 160) }}</p>

                                            <ul class="investigation-account-meta">

                                                @if ($investigation->owner)

                                                    <li><i class="fa-solid fa-user" aria-hidden="true"></i> Porteur : {{ $investigation->owner->name }}</li>

                                                @endif

                                                @if ($investigation->country)

                                                    <li><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $investigation->country }}</li>

                                                @endif

                                            </ul>

                                            <div class="investigation-account-actions">
                                                <a href="{{ route('account.investigations.show', $investigation) }}" class="ci-btn ci-btn--primary ci-btn--sm">
                                                    <i class="fa-solid fa-door-open" aria-hidden="true"></i> Ouvrir l'espace collaboratif
                                                </a>
                                            </div>

                                        </article>

                                    @endforeach

                                </div>

                            @endif

                        </div>

                    @elseif (in_array($tab, ['publications', 'sales', 'wallet', 'received_orders', 'orders', 'billing', 'notifications', 'profile'], true))

                        @include('partials.account-panels-marketplace')

                    @elseif ($tab === 'favorites')

                        <div class="account-panel">

                            <h2 class="form-heading"><i class="fa-solid fa-heart" aria-hidden="true"></i> Mes favoris</h2>

                            @if ($favorites->isEmpty())

                                <div class="empty-state-page empty-state-page--inline">

                                    <i class="fa-regular fa-heart" aria-hidden="true"></i>

                                    <p>Vous n'avez pas encore de contenu en favori.</p>

                                    <a href="{{ route('contents.index') }}" class="ci-btn ci-btn--primary">

                                        <i class="fa-solid fa-film" aria-hidden="true"></i> Parcourir les contenus

                                    </a>

                                </div>

                            @else

                                <div class="products-grid account-favorites-grid">

                                    @foreach ($favorites as $item)

                                        @include('partials.content-card', ['item' => $item])

                                    @endforeach

                                </div>

                            @endif

                        </div>

                    @elseif ($tab === 'purchases')

                        <div class="account-panel">

                            <h2 class="form-heading"><i class="fa-solid fa-bag-shopping" aria-hidden="true"></i> Mes achats</h2>



                            @if ($purchases->isEmpty())

                                <div class="empty-state-page empty-state-page--inline">

                                    <i class="fa-solid fa-film" aria-hidden="true"></i>

                                    <p>Vous n'avez pas encore acheté de contenu.</p>

                                    <a href="{{ route('contents.index') }}" class="ci-btn ci-btn--primary">

                                        <i class="fa-solid fa-film" aria-hidden="true"></i> Parcourir les contenus

                                    </a>

                                </div>

                            @else

                                <div class="purchases-list">

                                    @foreach ($purchases as $purchase)

                                        @if ($purchase->content)

                                            <article class="purchase-item">

                                                @if ($purchase->content->thumbnailUrl())

                                                    <img src="{{ $purchase->content->thumbnailUrl() }}" alt="" class="purchase-thumb">

                                                @endif

                                                <div class="purchase-info">

                                                    <h3><a href="{{ route('contents.show', $purchase->content->slug) }}">{{ $purchase->content->title }}</a></h3>

                                                    <p class="purchase-meta">

                                                        Acheté le {{ $purchase->purchased_at->format('d/m/Y') }}

                                                        · {{ number_format($purchase->price, 0) }} €

                                                        @if ($purchase->invoice_number)

                                                            · {{ $purchase->invoice_number }}

                                                        @endif

                                                    </p>

                                                </div>

                                                <div class="purchase-actions">
                                                    <a href="{{ route('contents.show', $purchase->content->slug) }}" class="ci-btn ci-btn--outline ci-btn--sm">
                                                        <i class="fa-solid fa-play" aria-hidden="true"></i> Accéder
                                                    </a>
                                                    @if ($purchase->content->hasDownloadableFile())
                                                        <a href="{{ route('contents.download', $purchase->content) }}" class="ci-btn ci-btn--primary ci-btn--sm">
                                                            <i class="fa-solid fa-download" aria-hidden="true"></i> Télécharger
                                                        </a>
                                                    @endif
                                                </div>

                                            </article>

                                        @endif

                                    @endforeach

                                </div>

                            @endif

                        </div>

                    @endif

                </div>

            </div>

        </div>

    </section>

@endsection

