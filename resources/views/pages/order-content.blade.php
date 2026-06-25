@extends('layouts.app')

@section('title', 'Commander un contenu – Collectinfos')

@section('content')
    <section class="page-hero page-hero--compact">
        <div class="container">
            <h1 class="page-title">Commander un sujet</h1>
            <p class="page-subtitle">Formulez une demande de contenu auprès de notre communauté de journalistes freelance.</p>
        </div>
    </section>

    <section class="section-page">
        <div class="container">
            @if (session('order_success'))
                <div class="ci-alert ci-alert--success">{{ session('order_success') }}</div>
            @endif

            @guest
                <div class="ci-alert ci-alert--info">
                    <a href="{{ route('account') }}">Connectez-vous</a> pour suivre vos commandes dans votre compte.
                </div>
            @endguest

            <form class="ci-form ci-form--wide" method="POST" action="{{ route('order-content.store') }}">
                @csrf
                <div class="form-group">
                    <label for="order-title">Titre du sujet *</label>
                    <input type="text" id="order-title" name="title" value="{{ old('title') }}" required>
                    @error('title')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="order-description">Description détaillée *</label>
                    <textarea id="order-description" name="description" rows="8" required placeholder="Thème, angle, zone géographique, format attendu, délais…">{{ old('description') }}</textarea>
                    @error('description')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="order-type">Type de contenu</label>
                        <select id="order-type" name="type">
                            <option value="">—</option>
                            @foreach ($types as $key => $label)
                                <option value="{{ $key }}" @selected(old('type') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="order-theme">Thématique</label>
                        <select id="order-theme" name="theme">
                            <option value="">—</option>
                            @foreach ($themes as $key => $label)
                                <option value="{{ $key }}" @selected(old('theme') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="order-country">Pays / zone</label>
                        <input type="text" id="order-country" name="country" value="{{ old('country') }}">
                    </div>
                    <div class="form-group">
                        <label for="order-region">Région précise</label>
                        <input type="text" id="order-region" name="region" value="{{ old('region') }}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="order-budget">Budget indicatif (€)</label>
                        <input type="number" id="order-budget" name="budget" min="0" step="0.01" value="{{ old('budget') }}">
                    </div>
                    <div class="form-group">
                        <label for="order-deadline">Date limite souhaitée</label>
                        <input type="date" id="order-deadline" name="deadline" value="{{ old('deadline') }}">
                    </div>
                </div>
                @auth
                    <button type="submit" class="ci-btn ci-btn--primary">
                        <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Envoyer la commande
                    </button>
                @else
                    <a href="{{ route('account') }}" class="ci-btn ci-btn--primary">Se connecter pour commander</a>
                @endauth
            </form>
        </div>
    </section>
@endsection
