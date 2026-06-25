@php
    $m = fn (string $key, mixed $default = '') => old($key, $user->meta($key, $default));
@endphp

<fieldset class="profile-form-section">
    <legend>Coordonnées</legend>
    <div class="form-row">
        <div class="form-group">
            <label for="profile-name">Nom complet</label>
            <input type="text" id="profile-name" name="name" value="{{ old('name', $user->name) }}" required>
        </div>
        <div class="form-group">
            <label for="profile-phone">Téléphone</label>
            <input type="tel" id="profile-phone" name="phone" value="{{ old('phone', $user->phone) }}">
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label for="profile-country">Pays</label>
            <input type="text" id="profile-country" name="country" value="{{ old('country', $user->country) }}">
        </div>
        <div class="form-group">
            <label for="profile-city">Ville / région de couverture</label>
            <input type="text" id="profile-city" name="city" value="{{ old('city', $user->city) }}">
        </div>
    </div>
    <div class="form-group">
        <label for="profile-type">Type de compte</label>
        <select id="profile-type" name="account_type">
            @foreach ($accountTypes as $key => $label)
                <option value="{{ $key }}" @selected(old('account_type', $user->account_type) === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label for="profile-bio">Bio / présentation</label>
        <textarea id="profile-bio" name="bio" rows="4">{{ old('bio', $user->bio) }}</textarea>
    </div>
</fieldset>

@if ($user->isJournalist())
    <fieldset class="profile-form-section">
        <legend>Profil professionnel — journaliste</legend>
        <div class="form-group">
            <label for="profile-specialties">Spécialités éditoriales</label>
            <input type="text" id="profile-specialties" name="specialties" value="{{ $m('specialties') }}" placeholder="politique, économie, sport…">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="profile-languages">Langues de travail</label>
                <input type="text" id="profile-languages" name="languages" value="{{ $m('languages') }}" placeholder="français, anglais…">
            </div>
            <div class="form-group">
                <label for="profile-nationality">Nationalité</label>
                <input type="text" id="profile-nationality" name="nationality" value="{{ $m('nationality') }}">
            </div>
        </div>
        <div class="form-group">
            <label for="profile-coverage">Zones géographiques couvertes</label>
            <input type="text" id="profile-coverage" name="coverage_zones" value="{{ $m('coverage_zones') }}" placeholder="Guinée — Kindia, Labé…">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="profile-experience">Années d'expérience</label>
                <input type="number" id="profile-experience" name="experience_years" min="0" max="60" value="{{ $m('experience_years') }}">
            </div>
            <div class="form-group">
                <label for="profile-press-card">N° carte de presse</label>
                <input type="text" id="profile-press-card" name="press_card" value="{{ $m('press_card') }}">
            </div>
        </div>
        <div class="form-group">
            <label for="profile-media-worked">Médias pour lesquels vous avez travaillé</label>
            <input type="text" id="profile-media-worked" name="media_worked" value="{{ $m('media_worked') }}">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="profile-portfolio">Portfolio (URL)</label>
                <input type="url" id="profile-portfolio" name="portfolio_url" value="{{ $m('portfolio_url') }}">
            </div>
            <div class="form-group">
                <label for="profile-linkedin">LinkedIn</label>
                <input type="url" id="profile-linkedin" name="linkedin_url" value="{{ $m('linkedin_url') }}">
            </div>
        </div>
        <div class="form-group">
            <label for="profile-twitter">X / Twitter</label>
            <input type="url" id="profile-twitter" name="twitter_url" value="{{ $m('twitter_url') }}">
        </div>
        <label class="checkbox-label">
            <input type="checkbox" name="public_profile" value="1" @checked($user->profile_slug)>
            Afficher mon profil public aux acheteurs
        </label>
        @if ($user->isProfileVerified())
            <p class="form-hint"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Profil validé par Collectinfos</p>
        @endif
    </fieldset>
@else
    <fieldset class="profile-form-section">
        <legend>Structure acheteur</legend>
        <div class="form-group">
            <label for="profile-org">Raison sociale / Nom du média ou organisation</label>
            <input type="text" id="profile-org" name="organization_name" value="{{ $m('organization_name') }}">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="profile-structure">Type de structure</label>
                <select id="profile-structure" name="structure_type">
                    <option value="">— Choisir —</option>
                    @foreach (config('collectinfos.buyer_structure_types', []) as $key => $label)
                        <option value="{{ $key }}" @selected($m('structure_type') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="profile-siret">SIRET / équivalent local</label>
                <input type="text" id="profile-siret" name="siret" value="{{ $m('siret') }}">
            </div>
        </div>
        <div class="form-group">
            <label for="profile-website">Site web officiel</label>
            <input type="url" id="profile-website" name="website" value="{{ $m('website') }}">
        </div>
        <div class="form-group">
            <label for="profile-org-address">Adresse du siège</label>
            <input type="text" id="profile-org-address" name="organization_address" value="{{ $m('organization_address') }}">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="profile-contact-name">Responsable de compte — Nom</label>
                <input type="text" id="profile-contact-name" name="contact_name" value="{{ $m('contact_name', $user->name) }}">
            </div>
            <div class="form-group">
                <label for="profile-contact-title">Fonction / poste</label>
                <input type="text" id="profile-contact-title" name="contact_title" value="{{ $m('contact_title') }}">
            </div>
        </div>
    </fieldset>

    <fieldset class="profile-form-section">
        <legend>Besoins éditoriaux</legend>
        <div class="form-group">
            <label for="profile-themes">Thématiques d'intérêt</label>
            <input type="text" id="profile-themes" name="editorial_themes" value="{{ $m('editorial_themes') }}">
        </div>
        <div class="form-group">
            <label for="profile-geo">Zones géographiques prioritaires</label>
            <input type="text" id="profile-geo" name="geo_priorities" value="{{ $m('geo_priorities') }}">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="profile-content-types">Types de contenus recherchés</label>
                <input type="text" id="profile-content-types" name="content_types_sought" value="{{ $m('content_types_sought') }}" placeholder="texte, photo, vidéo…">
            </div>
            <div class="form-group">
                <label for="profile-volume">Volume mensuel estimé</label>
                <input type="text" id="profile-volume" name="monthly_order_volume" value="{{ $m('monthly_order_volume') }}" placeholder="ex: 5 commandes / mois">
            </div>
        </div>
    </fieldset>
@endif

<fieldset class="profile-form-section">
    <legend>Paiement & facturation</legend>
    <div class="form-row">
        <div class="form-group">
            <label for="profile-payment">Mode de paiement préféré</label>
            <select id="profile-payment" name="payment_preference">
                <option value="">—</option>
                @foreach ($paymentMethods as $key => $method)
                    <option value="{{ $key }}" @selected($m('payment_preference') === $key)>{{ $method['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="profile-mobile-money">Mobile money</label>
            <input type="text" id="profile-mobile-money" name="mobile_money_number" value="{{ $m('mobile_money_number') }}">
        </div>
    </div>
    @if ($user->isJournalist())
        <div class="form-group">
            <label for="profile-bank">Coordonnées bancaires (reversements)</label>
            <textarea id="profile-bank" name="bank_details" rows="2">{{ $m('bank_details') }}</textarea>
        </div>
    @else
        <div class="form-group">
            <label for="profile-billing-address">Adresse de facturation</label>
            <input type="text" id="profile-billing-address" name="billing_address" value="{{ $m('billing_address') }}">
        </div>
        <div class="form-group">
            <label for="profile-billing-details">Coordonnées de facturation</label>
            <textarea id="profile-billing-details" name="billing_details" rows="2">{{ $m('billing_details') }}</textarea>
        </div>
    @endif
</fieldset>
