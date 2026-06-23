@extends('layouts.app')

@section('title', 'Mon compte – Collectinfos')

@section('content')
    <section class="page-hero page-hero--compact">
        <div class="container">
            <h1 class="page-title">Mon compte</h1>
            <p class="page-subtitle">Connectez-vous pour accéder à vos achats et contenus abonnés.</p>
        </div>
    </section>

    <section class="section-page">
        <div class="container">
            @if (session('error'))
                <div class="ci-alert ci-alert--error">{{ session('error') }}</div>
            @endif

            <div class="account-grid">
                <div class="page-form-wrap">
                    <h2 class="form-heading"><i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i> Connexion</h2>
                    <form class="ci-form" action="{{ route('account.login') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="login-email">E-mail</label>
                            <input type="email" id="login-email" name="email" value="{{ old('email') }}" placeholder="votre@email.com" required>
                            @error('email')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="login-password">Mot de passe</label>
                            <input type="password" id="login-password" name="password" required>
                        </div>
                        <label class="checkbox-label">
                            <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                            Se souvenir de moi
                        </label>
                        <button type="submit" class="ci-btn ci-btn--primary">
                            <i class="fa-solid fa-right-to-bracket" aria-hidden="true"></i> Se connecter
                        </button>
                    </form>
                </div>

                <div class="page-form-wrap">
                    <h2 class="form-heading"><i class="fa-solid fa-user-plus" aria-hidden="true"></i> Créer un compte</h2>
                    <p class="form-intro">Inscrivez-vous pour acheter des contenus, gérer vos achats et accéder à l'espace collaboration.</p>
                    <form class="ci-form" action="{{ route('account.register') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="register-name">Nom complet</label>
                            <input type="text" id="register-name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="register-email">E-mail</label>
                            <input type="email" id="register-email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="register-password">Mot de passe</label>
                            <input type="password" id="register-password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label for="register-password-confirm">Confirmer le mot de passe</label>
                            <input type="password" id="register-password-confirm" name="password_confirmation" required>
                            @error('password')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit" class="ci-btn ci-btn--primary">
                            <i class="fa-solid fa-user-plus" aria-hidden="true"></i> S'inscrire
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
