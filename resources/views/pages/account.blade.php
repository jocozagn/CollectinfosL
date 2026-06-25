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
                    <p class="form-intro">Inscrivez-vous pour acheter des contenus, commander des sujets ou déposer vos productions.</p>
                    <form class="ci-form" action="{{ route('account.register') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="register-name">Nom complet</label>
                            <input type="text" id="register-name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="register-phone">Téléphone</label>
                                <input type="text" id="register-phone" name="phone" value="{{ old('phone') }}">
                            </div>
                            <div class="form-group">
                                <label for="register-country">Pays</label>
                                <input type="text" id="register-country" name="country" value="{{ old('country') }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="register-city">Ville / région</label>
                            <input type="text" id="register-city" name="city" value="{{ old('city') }}">
                        </div>
                        <div class="form-group">
                            <label for="register-type">Type de profil</label>
                            <select id="register-type" name="account_type">
                                @foreach (config('collectinfos.account_types', []) as $key => $label)
                                    <option value="{{ $key }}" @selected(old('account_type') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
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
                        <div class="register-profile-sections" id="register-journalist-fields">
                            <div class="form-group">
                                <label for="register-specialties">Spécialités</label>
                                <input type="text" id="register-specialties" name="specialties" value="{{ old('specialties') }}" placeholder="politique, économie, sport…">
                            </div>
                            <div class="form-group">
                                <label for="register-languages">Langues</label>
                                <input type="text" id="register-languages" name="languages" value="{{ old('languages') }}">
                            </div>
                            <div class="form-group">
                                <label for="register-coverage">Zones couvertes</label>
                                <input type="text" id="register-coverage" name="coverage_zones" value="{{ old('coverage_zones') }}">
                            </div>
                        </div>
                        <div class="register-profile-sections" id="register-buyer-fields" hidden>
                            <div class="form-group">
                                <label for="register-org">Organisation</label>
                                <input type="text" id="register-org" name="organization_name" value="{{ old('organization_name') }}">
                            </div>
                            <div class="form-group">
                                <label for="register-structure">Type de structure</label>
                                <select id="register-structure" name="structure_type">
                                    <option value="">— Choisir —</option>
                                    @foreach (config('collectinfos.buyer_structure_types', []) as $key => $label)
                                        <option value="{{ $key }}" @selected(old('structure_type') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="register-themes">Thématiques recherchées</label>
                                <input type="text" id="register-themes" name="editorial_themes" value="{{ old('editorial_themes') }}">
                            </div>
                        </div>
                        <label class="checkbox-label checkbox-label--inline">
                            <input type="checkbox" name="journalist" id="register-journalist" value="1" @checked(old('journalist'))>
                            Je suis journaliste / correspondant
                        </label>
                        <button type="submit" class="ci-btn ci-btn--primary">
                            <i class="fa-solid fa-user-plus" aria-hidden="true"></i> S'inscrire
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var journalistTypes = ['journalist', 'correspondent', 'photographer', 'videographer', 'expert'];
            var typeSelect = document.getElementById('register-type');
            var journalistCheck = document.getElementById('register-journalist');
            var journalistFields = document.getElementById('register-journalist-fields');
            var buyerFields = document.getElementById('register-buyer-fields');

            function syncRegisterSections() {
                var isJournalist = (journalistCheck && journalistCheck.checked)
                    || (typeSelect && journalistTypes.indexOf(typeSelect.value) !== -1);

                if (journalistFields) journalistFields.hidden = !isJournalist;
                if (buyerFields) buyerFields.hidden = isJournalist;
            }

            if (typeSelect) typeSelect.addEventListener('change', syncRegisterSections);
            if (journalistCheck) journalistCheck.addEventListener('change', syncRegisterSections);
            syncRegisterSections();
        });
    </script>
@endpush
