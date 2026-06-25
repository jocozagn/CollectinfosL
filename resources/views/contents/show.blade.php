@extends('layouts.app')

@section('title', $content->title . ' – Collectinfos')

@section('content')
    @php
        $typeLabels = \App\Models\Content::typeLabels();
        $themeLabels = \App\Models\Content::themeLabels();
        $categoryLabels = \App\Models\Content::categoryLabels();
        $embedUrl = $content->youtubeEmbedId() ? 'https://www.youtube.com/embed/' . $content->youtubeEmbedId() : null;
        $mediaUrl = $hasAccess ? $content->deliveryMediaUrl(auth()->user()) : null;
    @endphp

    <article class="content-detail">
        <div class="container">
            @if (session('cart_success'))
                <div class="ci-alert ci-alert--success">{{ session('cart_success') }}</div>
            @endif
            @if (session('error'))
                <div class="ci-alert ci-alert--error">{{ session('error') }}</div>
            @endif

            <nav class="breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ route('home') }}"><i class="fa-solid fa-house" aria-hidden="true"></i> Accueil</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('contents.index') }}">Nos contenus</a>
                <span aria-hidden="true">/</span>
                <span>{{ Str::limit($content->trans('title'), 50) }}</span>
            </nav>

            <header class="content-header">
                <div class="content-badges">
                    <span class="badge badge-type badge-type-{{ $content->type }}">
                        {{ $typeLabels[$content->type] ?? $content->type }}
                    </span>
                    @if ($content->isFree())
                        <span class="badge badge-free"><i class="fa-solid fa-gift" aria-hidden="true"></i> Gratuit</span>
                    @elseif ($content->isExclusive() && $content->isSoldExclusively())
                        <span class="badge badge-sold"><i class="fa-solid fa-lock" aria-hidden="true"></i> Exclusivité vendue</span>
                    @elseif ($content->isExclusive())
                        <span class="badge badge-exclusive"><i class="fa-solid fa-gem" aria-hidden="true"></i> Exclusif — {{ number_format($content->price, 0) }} €</span>
                    @elseif ($content->isPaid())
                        <span class="badge badge-paid"><i class="fa-solid fa-tag" aria-hidden="true"></i> {{ number_format($content->price, 0) }} €</span>
                    @elseif ($content->access === 'subscriber')
                        <span class="badge badge-subscriber"><i class="fa-solid fa-lock" aria-hidden="true"></i> Abonnés</span>
                    @endif
                </div>

                <h1 class="content-title">{{ $content->trans('title') }}</h1>

                <div class="content-meta">
                    @if ($content->country)
                        <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $content->country }}</span>
                    @endif
                    @if ($content->theme)
                        <span><i class="fa-solid fa-tag" aria-hidden="true"></i> {{ $themeLabels[$content->theme] ?? $content->theme }}</span>
                    @endif
                    @if ($content->category)
                        <span><i class="fa-solid fa-folder" aria-hidden="true"></i> {{ $categoryLabels[$content->category] ?? $content->category }}</span>
                    @endif
                    @if ($content->duration)
                        <span><i class="fa-regular fa-clock" aria-hidden="true"></i> {{ $content->duration }}</span>
                    @endif
                    @if ($content->published_at)
                        <span><i class="fa-regular fa-calendar" aria-hidden="true"></i> {{ $content->published_at->format('d/m/Y') }}</span>
                    @endif
                </div>
            </header>

            <div class="content-layout">
                <div class="content-main">
                    <div class="content-media">
                        @if ($hasAccess)
                            @if ($embedUrl && in_array($content->type, ['video'], true))
                                <div class="media-embed">
                                    <iframe src="{{ $embedUrl }}" title="{{ $content->title }}" allowfullscreen loading="lazy"></iframe>
                                </div>
                            @elseif ($mediaUrl && $content->type === 'video' && preg_match('/\.(mp4|webm|ogg)(\?|$)/i', $mediaUrl))
                                <video class="media-player" controls poster="{{ $content->thumbnailUrl() }}">
                                    <source src="{{ $mediaUrl }}">
                                </video>
                            @elseif ($mediaUrl && $content->type === 'audio')
                                <audio class="media-player" controls>
                                    <source src="{{ $mediaUrl }}">
                                </audio>
                            @elseif ($content->thumbnailUrl())
                                <img src="{{ $content->thumbnailUrl() }}" alt="{{ $content->title }}" class="media-image">
                            @endif
                        @else
                            <div class="content-paywall">
                                @if ($content->thumbnailUrl())
                                    <img src="{{ $content->thumbnailUrl() }}" alt="" class="paywall-bg" aria-hidden="true">
                                @endif
                                <div class="paywall-overlay">
                                    <i class="fa-solid fa-lock paywall-lock" aria-hidden="true"></i>
                                    <h2>{{ __('site.contents.premium') }}</h2>
                                    <p>
                                        @if ($content->isExclusive())
                                            Cette exclusivité est vendue à un seul acheteur. Une fois achetée, elle ne sera plus disponible.
                                        @else
                                            Ce contenu est disponible à l'achat ou pour les abonnés Collectinfos.
                                        @endif
                                    </p>
                                    <div class="paywall-actions">
                                        @if ($content->hasPreview())
                                            <button
                                                type="button"
                                                class="btn-paywall btn-paywall--preview action-preview"
                                                data-preview-title="{{ e($content->title) }}"
                                                data-preview-mode="{{ $content->previewMode() }}"
                                                data-preview-seconds="{{ $content->preview_seconds ?? 15 }}"
                                                data-preview-url="{{ $content->previewMediaUrl() ?? '' }}"
                                                data-preview-embed="{{ $content->previewEmbedUrl() ?? '' }}"
                                                data-preview-text="{{ e(str_replace(["\r", "\n"], ' ', $content->previewText() ?? '')) }}"
                                            >
                                                <span class="btn-paywall-icon" aria-hidden="true"><i class="fa-solid fa-circle-play"></i></span>
                                                <span>{{ __('site.contents.preview') }}</span>
                                            </button>
                                        @endif
                                        @if ($content->isPurchasable())
                                            <form action="{{ route('cart.add', $content) }}" method="POST" class="paywall-form">
                                                @csrf
                                                <button type="submit" class="btn-paywall btn-paywall--buy">
                                                    <span class="btn-paywall-icon" aria-hidden="true"><i class="fa-solid fa-cart-shopping"></i></span>
                                                    <span>
                                                        @if ($content->isExclusive())
                                                            {{ __('site.contents.buy') }} l'exclusivité — {{ number_format($content->price, 0) }} €
                                                        @else
                                                            {{ __('site.contents.buy') }} — {{ number_format($content->price, 0) }} €
                                                        @endif
                                                    </span>
                                                </button>
                                            </form>
                                        @elseif ($content->isSubscriberOnly() && ! $hasAccess)
                                            <a href="{{ route('products') }}" class="btn-paywall btn-paywall--buy">
                                                <span class="btn-paywall-icon" aria-hidden="true"><i class="fa-solid fa-id-card"></i></span>
                                                <span>S'abonner pour accéder</span>
                                            </a>
                                        @elseif ($content->isExclusive() && $content->isSoldExclusively())
                                            <p class="paywall-sold-notice"><i class="fa-solid fa-lock" aria-hidden="true"></i> Cette exclusivité a déjà été acquise par un acheteur.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if ($content->trans('summary'))
                        <p class="content-summary">{{ $content->trans('summary') }}</p>
                    @endif

                    @if ($content->trans('body') && $hasAccess)
                        <div class="content-body">
                            {!! nl2br(e($content->trans('body'))) !!}
                        </div>
                    @elseif ($content->trans('body') && $content->isPaid() && ! $hasAccess)
                        <div class="content-body content-body--truncated">
                            {!! nl2br(e(Str::limit($content->trans('body'), 300))) !!}
                            <p class="content-more"><i class="fa-solid fa-lock" aria-hidden="true"></i> Contenu complet disponible après achat.</p>
                        </div>
                    @endif
                </div>

                <aside class="content-sidebar">
                    <div class="sidebar-box">
                        <h3>Actions</h3>
                        @if ($content->isPurchasable())
                            <form action="{{ route('cart.add', $content) }}" method="POST">
                                @csrf
                                <button type="submit" class="sidebar-btn sidebar-btn--primary">
                                    <i class="fa-solid fa-cart-plus" aria-hidden="true"></i>
                                    @if ($content->isExclusive())
                                        Acheter l'exclusivité
                                    @else
                                        {{ __('site.contents.add_cart') }}
                                    @endif
                                </button>
                            </form>
                        @elseif ($content->isExclusive() && $content->isSoldExclusively())
                            <span class="sidebar-badge sidebar-badge--sold"><i class="fa-solid fa-lock" aria-hidden="true"></i> Exclusivité vendue</span>
                        @elseif ($hasAccess && ($content->isPaid() || $content->isExclusive()))
                            <span class="sidebar-badge"><i class="fa-solid fa-check" aria-hidden="true"></i> {{ __('site.contents.purchased') }}</span>
                            @if ($content->hasDownloadableFile())
                                <a href="{{ route('contents.download', $content) }}" class="sidebar-btn sidebar-btn--primary">
                                    <i class="fa-solid fa-download" aria-hidden="true"></i> Télécharger
                                </a>
                            @endif
                        @else
                            <a href="{{ route('contents.index') }}" class="sidebar-btn">
                                <i class="fa-solid fa-table-cells" aria-hidden="true"></i> Tous les contenus
                            </a>
                        @endif
                        <button
                            type="button"
                            class="sidebar-btn action-favorite"
                            data-slug="{{ $content->slug }}"
                            title="Ajouter aux favoris"
                            aria-label="Ajouter aux favoris"
                            aria-pressed="false"
                        >
                            <i class="fa-regular fa-heart" aria-hidden="true"></i>
                            <span class="favorite-label">Ajouter aux favoris</span>
                        </button>
                        <button type="button" class="sidebar-btn" onclick="navigator.share?.({title: '{{ addslashes($content->title) }}', url: location.href})">
                            <i class="fa-solid fa-share-nodes" aria-hidden="true"></i> Partager
                        </button>
                    </div>

                    @if ($content->author)
                        <div class="sidebar-box">
                            <h3>Publié par</h3>
                            <p class="author-name"><i class="fa-solid fa-user-pen" aria-hidden="true"></i> {{ $content->author->name }}</p>
                        </div>
                    @endif
                </aside>
            </div>
        </div>
    </article>

    @if ($related->isNotEmpty())
        <section class="section-related">
            <div class="container">
                <div class="section-title">
                    <h4>{{ __('site.contents.similar') }}</h4>
                </div>
                <div class="products-grid products-grid--4">
                    @foreach ($related as $item)
                        @include('partials.content-card', ['item' => $item])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
