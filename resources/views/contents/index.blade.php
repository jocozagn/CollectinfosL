@extends('layouts.app')

@section('title', __('site.contents.title').' – Collectinfos')

@section('content')
    <section class="page-hero page-hero--compact">
        <div class="container">
            <h1 class="page-title">{{ __('site.contents.title') }}</h1>
            <p class="page-subtitle">{{ __('site.contents.subtitle') }}</p>
        </div>
    </section>

    <section class="section-catalog">
        <div class="container">
            <aside class="catalog-filters">
                <form method="GET" action="{{ route('contents.index') }}" class="filters-form">
                    <h2 class="filters-title"><i class="fa-solid fa-filter" aria-hidden="true"></i> Filtrer</h2>

                    <div class="filter-group">
                        <label for="q">Recherche</label>
                        <input type="text" id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Titre, pays, mot-clé…">
                    </div>

                    <div class="filter-group">
                        <label for="type">Type</label>
                        <select id="type" name="type">
                            <option value="">Tous les types</option>
                            @foreach ($types as $key => $label)
                                <option value="{{ $key }}" @selected(($filters['type'] ?? '') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="categorie">Catégorie</label>
                        <select id="categorie" name="categorie">
                            <option value="">Toutes les catégories</option>
                            @foreach ($categories as $key => $label)
                                <option value="{{ $key }}" @selected(($filters['categorie'] ?? '') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="theme">Thème</label>
                        <select id="theme" name="theme">
                            <option value="">Tous les thèmes</option>
                            @foreach ($themes as $key => $label)
                                <option value="{{ $key }}" @selected(($filters['theme'] ?? '') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="access">Accès</label>
                        <select id="access" name="access">
                            <option value="">Tous les accès</option>
                            <option value="free" @selected(($filters['access'] ?? '') === 'free')>Gratuit</option>
                            <option value="subscriber" @selected(($filters['access'] ?? '') === 'subscriber')>Abonnés</option>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn-filter"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i> Appliquer</button>
                        <a href="{{ route('contents.index') }}" class="btn-filter btn-filter--outline">Réinitialiser</a>
                    </div>
                </form>
            </aside>

            <div class="catalog-results">
                <div class="catalog-toolbar">
                    <p class="catalog-count">
                        <strong>{{ $total }}</strong> contenu{{ $total > 1 ? 's' : '' }} trouvé{{ $total > 1 ? 's' : '' }}
                    </p>
                </div>

                @if ($contents->isEmpty())
                    <div class="empty-catalog">
                        <i class="fa-solid fa-folder-open" aria-hidden="true"></i>
                        <p>Aucun contenu ne correspond à vos critères.</p>
                        <a href="{{ route('contents.index') }}" class="btn-filter">Voir tous les contenus</a>
                    </div>
                @else
                    <div class="products-grid">
                        @foreach ($contents as $item)
                            @include('partials.content-card', ['item' => $item])
                        @endforeach
                    </div>

                    @if ($contents->hasPages())
                        <div class="pagination-wrap">
                            {{ $contents->links('partials.pagination') }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </section>
@endsection
