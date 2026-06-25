@extends('admin.layouts.app')

@section('title', $content->exists ? 'Modifier le contenu' : 'Nouveau contenu')
@section('page-title', $content->exists ? 'Modifier le contenu' : 'Publier un nouveau contenu')

@section('content')
    <form
        class="admin-form"
        method="POST"
        action="{{ $content->exists ? route('admin.contents.update', $content) : route('admin.contents.store') }}"
        enctype="multipart/form-data"
    >
        @csrf
        @if ($content->exists)
            @method('PUT')
        @endif

        <div class="form-grid">
            <div class="form-main">
                <div class="form-group">
                    <label for="title">Titre *</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $content->title) }}" required>
                </div>

                <div class="form-group">
                    <label for="summary">Résumé</label>
                    <textarea id="summary" name="summary" rows="3">{{ old('summary', $content->summary) }}</textarea>
                </div>

                <div class="form-group">
                    <label for="body">Description / contenu</label>
                    <textarea id="body" name="body" rows="10">{{ old('body', $content->body) }}</textarea>
                </div>

                @foreach (['en' => 'English', 'pt' => 'Português'] as $locale => $label)
                    <div class="sidebar-box" style="margin-top: 8px;">
                        <h3>Traduction — {{ $label }}</h3>
                        <div class="form-group">
                            <label for="translations_{{ $locale }}_title">Titre ({{ strtoupper($locale) }})</label>
                            <input type="text" id="translations_{{ $locale }}_title" name="translations[{{ $locale }}][title]" value="{{ old("translations.{$locale}.title", $translations[$locale]['title'] ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label for="translations_{{ $locale }}_summary">Résumé</label>
                            <textarea id="translations_{{ $locale }}_summary" name="translations[{{ $locale }}][summary]" rows="2">{{ old("translations.{$locale}.summary", $translations[$locale]['summary'] ?? '') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="translations_{{ $locale }}_body">Contenu</label>
                            <textarea id="translations_{{ $locale }}_body" name="translations[{{ $locale }}][body]" rows="5">{{ old("translations.{$locale}.body", $translations[$locale]['body'] ?? '') }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="translations_{{ $locale }}_preview_excerpt">Extrait aperçu</label>
                            <textarea id="translations_{{ $locale }}_preview_excerpt" name="translations[{{ $locale }}][preview_excerpt]" rows="2">{{ old("translations.{$locale}.preview_excerpt", $translations[$locale]['preview_excerpt'] ?? '') }}</textarea>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="form-sidebar">
                <div class="sidebar-box">
                    <h3>Publication</h3>
                    <div class="form-group">
                        <label for="status">Statut *</label>
                        <select id="status" name="status" required>
                            <option value="draft" @selected(old('status', $content->status) === 'draft')>Brouillon</option>
                            <option value="published" @selected(old('status', $content->status) === 'published')>Publié</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="access">Accès *</label>
                        <select id="access" name="access" required>
                            <option value="free" @selected(old('access', $content->access) === 'free')>Libre</option>
                            <option value="subscriber" @selected(old('access', $content->access) === 'subscriber')>Réservé aux abonnés</option>
                            <option value="exclusive" @selected(old('access', $content->access) === 'exclusive')>Exclusif — 1 seul acheteur</option>
                        </select>
                        <small class="form-hint">L'exclusivité ne peut être achetée qu'une seule fois (vidéo, article, reportage…).</small>
                    </div>
                    <div class="form-group">
                        <label for="price">Prix (€)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" value="{{ old('price', $content->price) }}">
                        @error('price')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                        @if ($content->isExclusive() && $content->isSoldExclusively())
                            <small class="form-hint">
                                Exclusivité vendue
                                @if ($content->exclusiveBuyer())
                                    à {{ $content->exclusiveBuyer()->name }}
                                @endif.
                            </small>
                        @endif
                    </div>
                    <div class="form-group">
                        <label for="price_gnf">Prix (GNF)</label>
                        <input type="number" id="price_gnf" name="price_gnf" step="1" min="0" value="{{ old('price_gnf', $content->price_gnf) }}" placeholder="ex: 500 000">
                        <small class="form-hint">Montant encaissé via Djomy. Obligatoire si le prix en € est renseigné.</small>
                        @error('price_gnf')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="sidebar-box">
                    <h3>Classification</h3>
                    <div class="form-group">
                        <label for="type">Type *</label>
                        <select id="type" name="type" required>
                            @foreach ($types as $key => $label)
                                <option value="{{ $key }}" @selected(old('type', $content->type) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="theme">Thème</label>
                        <select id="theme" name="theme">
                            <option value="">— Choisir —</option>
                            @foreach ($themes as $key => $label)
                                <option value="{{ $key }}" @selected(old('theme', $content->theme) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="category">Catégorie</label>
                        <select id="category" name="category">
                            <option value="">— Choisir —</option>
                            @foreach ($categories as $key => $label)
                                <option value="{{ $key }}" @selected(old('category', $content->category) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="country">Pays</label>
                        <input type="text" id="country" name="country" value="{{ old('country', $content->country) }}" placeholder="ex: Guinée, Mali...">
                    </div>
                    <div class="form-group">
                        <label for="duration">Durée</label>
                        <input type="text" id="duration" name="duration" value="{{ old('duration', $content->duration) }}" placeholder="ex: 2 min 30">
                    </div>
                </div>

                <div class="sidebar-box">
                    <h3>Aperçu public</h3>
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="preview_enabled" value="1" @checked(old('preview_enabled', $content->preview_enabled ?? true))>
                            Activer l'aperçu sur le site
                        </label>
                    </div>
                    <div class="form-group">
                        <label for="preview_seconds">Durée vidéo/audio (secondes)</label>
                        <input type="number" id="preview_seconds" name="preview_seconds" min="5" max="120" value="{{ old('preview_seconds', $content->preview_seconds ?? 15) }}">
                        <small class="form-hint">Nombre de secondes visibles en aperçu (5 à 120).</small>
                    </div>
                    <div class="form-group">
                        <label for="preview_excerpt">Extrait article / texte d'aperçu</label>
                        <textarea id="preview_excerpt" name="preview_excerpt" rows="4" placeholder="Paragraphe affiché en aperçu pour les articles…">{{ old('preview_excerpt', $content->preview_excerpt) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="preview_media_file">Clip d'aperçu (optionnel)</label>
                        @if ($content->preview_media_path && ! str_starts_with($content->preview_media_path, 'http'))
                            <p class="current-file">{{ basename($content->preview_media_path) }}</p>
                        @endif
                        <input type="file" id="preview_media_file" name="preview_media_file" accept="video/*,audio/*">
                        <small class="form-hint">Court extrait pour contenus payants. Sinon le média principal est utilisé.</small>
                    </div>
                    <div class="form-group">
                        <label for="preview_media_url">Ou URL d'aperçu (YouTube, Vimeo…)</label>
                        <input type="url" id="preview_media_url" name="preview_media_url" value="{{ old('preview_media_url', str_starts_with($content->preview_media_path ?? '', 'http') ? $content->preview_media_path : '') }}" placeholder="https://...">
                    </div>
                </div>

                <div class="sidebar-box">
                    <h3>Médias</h3>
                    <div class="form-group">
                        <label for="thumbnail">Image de couverture</label>
                        @if ($content->thumbnailUrl())
                            <img src="{{ $content->thumbnailUrl() }}" alt="" class="current-media">
                        @endif
                        <input type="file" id="thumbnail" name="thumbnail" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label for="media_file">Fichier média (vidéo, audio, PDF…)</label>
                        @if ($content->media_path)
                            <p class="current-file">{{ basename($content->media_path) }}</p>
                        @endif
                        <input type="file" id="media_file" name="media_file">
                    </div>
                    <div class="form-group">
                        <label for="media_url">Ou URL externe (YouTube, Vimeo…)</label>
                        <input type="url" id="media_url" name="media_url" value="{{ old('media_url') }}" placeholder="https://...">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.contents.index') }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">
                {{ $content->exists ? 'Enregistrer les modifications' : 'Publier le contenu' }}
            </button>
        </div>
    </form>
@endsection
