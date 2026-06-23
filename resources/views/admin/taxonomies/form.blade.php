@extends('admin.layouts.app')

@section('title', $taxonomy->exists ? 'Modifier' : 'Ajouter')
@section('page-title', ($taxonomy->exists ? 'Modifier' : 'Ajouter').' — '.$kindLabel)

@section('content')
    <form
        class="admin-form"
        method="POST"
        action="{{ $taxonomy->exists ? route('admin.taxonomies.update', [$kindRoute, $taxonomy]) : route('admin.taxonomies.store', $kindRoute) }}"
        enctype="multipart/form-data"
    >
        @csrf
        @if ($taxonomy->exists)
            @method('PUT')
        @endif

        <div class="form-grid">
            <div class="form-main">
                <div class="form-group">
                    <label for="name">Nom *</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $taxonomy->name) }}" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4">{{ old('description', $taxonomy->description) }}</textarea>
                </div>

                @if ($kind === \App\Models\Taxonomy::KIND_TYPE)
                    <div class="form-group">
                        <label for="icon">Icône Font Awesome</label>
                        <input type="text" id="icon" name="icon" value="{{ old('icon', $taxonomy->icon) }}" placeholder="ex: fa-film">
                        <small class="form-hint">Classe sans le préfixe fa-solid (ex: fa-film, fa-newspaper).</small>
                    </div>
                @endif

                @if ($kind === \App\Models\Taxonomy::KIND_CATEGORY)
                    <div class="form-group">
                        <label for="image">Image</label>
                        @if ($taxonomy->imageUrl())
                            <img src="{{ $taxonomy->imageUrl() }}" alt="" class="current-media">
                        @endif
                        <input type="file" id="image" name="image" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label for="image_url">Ou URL d'image</label>
                        <input type="url" id="image_url" name="image_url" value="{{ old('image_url', str_starts_with($taxonomy->image ?? '', 'http') ? $taxonomy->image : '') }}" placeholder="https://...">
                    </div>
                @endif

                @foreach (['en' => 'English', 'pt' => 'Português'] as $locale => $label)
                    <div class="locale-block">
                        <h3 class="locale-block-title"><i class="fa-solid fa-language" aria-hidden="true"></i> {{ $label }}</h3>
                        <div class="form-group">
                            <label for="translations_{{ $locale }}_name">Nom ({{ strtoupper($locale) }})</label>
                            <input type="text" id="translations_{{ $locale }}_name" name="translations[{{ $locale }}][name]" value="{{ old("translations.{$locale}.name", $translations[$locale]['name'] ?? '') }}">
                        </div>
                        <div class="form-group">
                            <label for="translations_{{ $locale }}_description">Description</label>
                            <textarea id="translations_{{ $locale }}_description" name="translations[{{ $locale }}][description]" rows="3">{{ old("translations.{$locale}.description", $translations[$locale]['description'] ?? '') }}</textarea>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="form-sidebar">
                <div class="sidebar-box">
                    <h3>Options</h3>
                    <div class="form-group">
                        <label for="sort_order">Ordre d'affichage</label>
                        <input type="number" id="sort_order" name="sort_order" min="0" value="{{ old('sort_order', $taxonomy->sort_order) }}">
                    </div>
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $taxonomy->is_active ?? true))>
                            Actif
                        </label>
                    </div>
                    @if ($kind === \App\Models\Taxonomy::KIND_CATEGORY)
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="show_on_home" value="1" @checked(old('show_on_home', $taxonomy->show_on_home))>
                                Afficher sur la page d'accueil
                            </label>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.taxonomies.index', $kindRoute) }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">
                {{ $taxonomy->exists ? 'Enregistrer' : 'Créer' }}
            </button>
        </div>
    </form>
@endsection
