@extends('layouts.app')

@section('title', 'Mon compte – Collectinfos')

@section('content')
    <section class="page-hero page-hero--compact">
        <div class="container">
            <h1 class="page-title">Bonjour, {{ $user->name }}</h1>
            <p class="page-subtitle">Gérez vos achats et accédez à vos contenus.</p>
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
                        <p><i class="fa-solid fa-user" aria-hidden="true"></i> {{ $user->name }}</p>
                        <p><i class="fa-solid fa-envelope" aria-hidden="true"></i> {{ $user->email }}</p>
                        <p><i class="fa-solid fa-film" aria-hidden="true"></i> {{ $purchases->count() }} contenu(s) acheté(s)</p>
                    </div>
                    <form action="{{ route('account.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="ci-btn ci-btn--outline ci-btn--block">
                            <i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i> Déconnexion
                        </button>
                    </form>
                </div>

                <div class="account-main">
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
                                            </p>
                                        </div>
                                        <a href="{{ route('contents.show', $purchase->content->slug) }}" class="ci-btn ci-btn--outline ci-btn--sm">
                                            <i class="fa-solid fa-play" aria-hidden="true"></i> Accéder
                                        </a>
                                    </article>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
