@extends('layouts.app')

@section('title', 'Proposer un contenu – Collectinfos')

@section('content')
    <section class="page-hero page-hero--compact">
        <div class="container">
            <h1 class="page-title">Proposer un contenu</h1>
            <p class="page-subtitle">Déposez votre reportage, photo, vidéo ou article. Notre équipe le validera avant publication.</p>
        </div>
    </section>

    <section class="section-page">
        <div class="container">
            <form class="ci-form ci-form--wide" method="POST" action="{{ route('submit-content.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-section">
                    <h2 class="form-section-title">Informations générales</h2>
                    <div class="form-group">
                        <label for="title">Titre *</label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" required>
                        @error('title')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-group">
                        <label for="summary">Pitch / résumé (250 mots max) *</label>
                        <textarea id="summary" name="summary" rows="5" maxlength="2500" required>{{ old('summary') }}</textarea>
                        @error('summary')<span class="form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="type">Type *</label>
                            <select id="type" name="type" required>
                                @foreach ($types as $key => $label)
                                    <option value="{{ $key }}" @selected(old('type') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="theme">Thématique</label>
                            <select id="theme" name="theme">
                                <option value="">—</option>
                                @foreach ($themes as $key => $label)
                                    <option value="{{ $key }}" @selected(old('theme') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="category">Section</label>
                            <select id="category" name="category">
                                <option value="">—</option>
                                @foreach ($categories as $key => $label)
                                    <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="keywords">Mots-clés</label>
                        <input type="text" id="keywords" name="keywords" value="{{ old('keywords') }}" placeholder="politique, élections, Conakry…">
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="form-section-title">Localisation</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="country">Pays</label>
                            <input type="text" id="country" name="country" value="{{ old('country') }}">
                        </div>
                        <div class="form-group">
                            <label for="region">Région</label>
                            <input type="text" id="region" name="region" value="{{ old('region') }}">
                        </div>
                        <div class="form-group">
                            <label for="city">Ville / zone</label>
                            <input type="text" id="city" name="city" value="{{ old('city') }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="gps_lat">Latitude GPS</label>
                            <input type="text" id="gps_lat" name="gps_lat" value="{{ old('gps_lat') }}">
                        </div>
                        <div class="form-group">
                            <label for="gps_lng">Longitude GPS</label>
                            <input type="text" id="gps_lng" name="gps_lng" value="{{ old('gps_lng') }}">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h2 class="form-section-title">Conditions de vente</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="access">Statut *</label>
                            <select id="access" name="access" required>
                                @foreach ($accessOptions as $key => $label)
                                    <option value="{{ $key }}" @selected(old('access', 'free') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="price">Prix (€)</label>
                            <input type="number" id="price" name="price" min="0" step="0.01" value="{{ old('price') }}">
                            @error('price')<span class="form-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="rights">Droits de diffusion</label>
                            <select id="rights" name="rights">
                                <option value="">—</option>
                                @foreach ($rightsOptions as $key => $label)
                                    <option value="{{ $key }}" @selected(old('rights') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <label class="checkbox-label">
                        <input type="checkbox" name="negotiable" value="1" @checked(old('negotiable'))>
                        Prix négociable
                    </label>
                </div>

                <div class="form-section">
                    <h2 class="form-section-title">Fichiers</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="thumbnail">Image de couverture</label>
                            <input type="file" id="thumbnail" name="thumbnail" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label for="media_file">Fichier média</label>
                            <input type="file" id="media_file" name="media_file">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="resolution">Résolution / qualité</label>
                            <input type="text" id="resolution" name="resolution" value="{{ old('resolution') }}">
                        </div>
                        <div class="form-group">
                            <label for="duration">Durée</label>
                            <input type="text" id="duration" name="duration" value="{{ old('duration') }}">
                        </div>
                        <div class="form-group">
                            <label for="content_date">Date de réalisation</label>
                            <input type="date" id="content_date" name="content_date" value="{{ old('content_date') }}">
                        </div>
                    </div>
                </div>

                <button type="submit" class="ci-btn ci-btn--primary">
                    <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Soumettre pour validation
                </button>
            </form>
        </div>
    </section>
@endsection
