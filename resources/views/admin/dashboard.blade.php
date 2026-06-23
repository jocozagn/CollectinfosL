@extends('admin.layouts.app')

@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('content')
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-value">{{ $totalContents }}</span>
            <span class="stat-label">Total contenus</span>
        </div>
        <div class="stat-card stat-success">
            <span class="stat-value">{{ $publishedContents }}</span>
            <span class="stat-label">Publiés</span>
        </div>
        <div class="stat-card stat-warning">
            <span class="stat-value">{{ $draftContents }}</span>
            <span class="stat-label">Brouillons</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ $contactMessages }}</span>
            <span class="stat-label">Messages contact</span>
        </div>
        <div class="stat-card stat-warning">
            <span class="stat-value">{{ $pendingRequests }}</span>
            <span class="stat-label">Candidatures en attente</span>
        </div>
        <div class="stat-card">
            <span class="stat-value">{{ $newsletterCount }}</span>
            <span class="stat-label">Abonnés newsletter</span>
        </div>
        <div class="stat-card stat-success">
            <span class="stat-value">{{ $openInvestigations }}</span>
            <span class="stat-label">Enquêtes ouvertes</span>
        </div>
    </div>

    <div class="admin-panel dashboard-shortcuts">
        <div class="panel-header">
            <h2>Gestion du site</h2>
        </div>
        <div class="shortcut-grid">
            <a href="{{ route('admin.taxonomies.create', 'categories') }}" class="shortcut-card">
                <i class="fa-solid fa-folder-tree" aria-hidden="true"></i>
                <strong>Nouvelle catégorie</strong>
                <span>{{ $categoryCount }} enregistrée(s)</span>
            </a>
            <a href="{{ route('admin.taxonomies.create', 'themes') }}" class="shortcut-card">
                <i class="fa-solid fa-tags" aria-hidden="true"></i>
                <strong>Nouveau thème</strong>
                <span>{{ $themeCount }} enregistré(s)</span>
            </a>
            <a href="{{ route('admin.taxonomies.create', 'types') }}" class="shortcut-card">
                <i class="fa-solid fa-shapes" aria-hidden="true"></i>
                <strong>Nouveau type</strong>
                <span>{{ $typeCount }} enregistré(s)</span>
            </a>
            <a href="{{ route('admin.site-stats.create') }}" class="shortcut-card">
                <i class="fa-solid fa-chart-simple" aria-hidden="true"></i>
                <strong>Statistique accueil</strong>
                <span>{{ $siteStatsCount }} chiffre(s)</span>
            </a>
            <a href="{{ route('admin.contents.create') }}" class="shortcut-card">
                <i class="fa-solid fa-circle-plus" aria-hidden="true"></i>
                <strong>Publier un contenu</strong>
                <span>Article, vidéo, audio…</span>
            </a>
            <a href="{{ route('admin.investigations.create') }}" class="shortcut-card">
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <strong>Nouvelle enquête</strong>
                <span>Collaboration journalistes</span>
            </a>
            <a href="{{ route('admin.partners.create') }}" class="shortcut-card">
                <i class="fa-solid fa-handshake-angle" aria-hidden="true"></i>
                <strong>Partenaire média</strong>
                <span>{{ $partnerCount }} logo(s)</span>
            </a>
            <a href="{{ route('admin.products.create') }}" class="shortcut-card">
                <i class="fa-solid fa-box-open" aria-hidden="true"></i>
                <strong>Nouvelle offre</strong>
                <span>{{ $productCount }} produit(s)</span>
            </a>
            <a href="{{ route('admin.settings.contact') }}" class="shortcut-card">
                <i class="fa-solid fa-address-card" aria-hidden="true"></i>
                <strong>Coordonnées</strong>
                <span>Tél., e-mail, zone</span>
            </a>
            <a href="{{ route('admin.settings.press') }}" class="shortcut-card">
                <i class="fa-solid fa-bullhorn" aria-hidden="true"></i>
                <strong>Relations presse</strong>
                <span>Texte & services</span>
            </a>
            <a href="{{ route('admin.settings.fact-checking') }}" class="shortcut-card">
                <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
                <strong>Fact-checking</strong>
                <span>Critères & intro</span>
            </a>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="admin-panel">
            <div class="panel-header">
                <h2>Derniers contenus</h2>
                <a href="{{ route('admin.contents.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus" aria-hidden="true"></i> Publier</a>
            </div>

            @if ($recentContents->isEmpty())
                <p class="empty-state">Aucun contenu. <a href="{{ route('admin.contents.create') }}">Créer le premier</a>.</p>
            @else
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Titre</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentContents as $item)
                            <tr>
                                <td>{{ Str::limit($item->title, 40) }}</td>
                                <td>
                                    <span @class(['badge', 'badge-published' => $item->status === 'published', 'badge-draft' => $item->status === 'draft'])>
                                        {{ $item->status === 'published' ? 'Publié' : 'Brouillon' }}
                                    </span>
                                </td>
                                <td>{{ $item->created_at->format('d/m/Y') }}</td>
                                <td><a href="{{ route('admin.contents.edit', $item) }}">Modifier</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="admin-panel">
            <div class="panel-header">
                <h2>Messages récents</h2>
                <a href="{{ route('admin.messages.index') }}" class="btn btn-secondary btn-sm">Voir tout</a>
            </div>

            @if ($recentMessages->isEmpty())
                <p class="empty-state">Aucun message.</p>
            @else
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Sujet</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentMessages as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ Str::limit($item->subject, 35) }}</td>
                                <td><a href="{{ route('admin.messages.show', $item) }}">Lire</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="admin-panel">
            <div class="panel-header">
                <h2>Candidatures récentes</h2>
                <a href="{{ route('admin.collaboration.index') }}" class="btn btn-secondary btn-sm">Voir tout</a>
            </div>

            @if ($recentRequests->isEmpty())
                <p class="empty-state">Aucune candidature.</p>
            @else
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentRequests as $item)
                            <tr>
                                <td>{{ $item->name }}</td>
                                <td>{{ $item->type === 'join' ? 'Rejoindre' : 'Proposer' }}</td>
                                <td>
                                    <span @class([
                                        'badge',
                                        'badge-pending' => $item->status === 'pending',
                                        'badge-accepted' => $item->status === 'accepted',
                                        'badge-rejected' => $item->status === 'rejected',
                                    ])>
                                        @switch($item->status)
                                            @case('accepted') Acceptée @break
                                            @case('rejected') Refusée @break
                                            @default En attente
                                        @endswitch
                                    </span>
                                </td>
                                <td><a href="{{ route('admin.collaboration.show', $item) }}">Voir</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection
