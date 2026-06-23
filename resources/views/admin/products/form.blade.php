@extends('admin.layouts.app')

@section('title', $product->exists ? 'Modifier l\'offre' : 'Nouvelle offre')
@section('page-title', $product->exists ? 'Modifier l\'offre' : 'Nouvelle offre')

@section('content')
    <form
        class="admin-form"
        method="POST"
        action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
        style="max-width: 560px;"
    >
        @csrf
        @if ($product->exists)
            @method('PUT')
        @endif

        <div class="form-group">
            <label for="name">Nom *</label>
            <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required>
        </div>

        <div class="form-group">
            <label for="description">Description *</label>
            <textarea id="description" name="description" rows="4" required>{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="form-group">
            <label for="price">Tarif affiché *</label>
            <input type="text" id="price" name="price" value="{{ old('price', $product->price) }}" placeholder="ex: 29 €/mois ou Sur devis" required>
        </div>

        <div class="form-group">
            <label for="icon">Icône Font Awesome *</label>
            <input type="text" id="icon" name="icon" value="{{ old('icon', $product->icon) }}" placeholder="fa-newspaper" required>
            <small class="form-hint">Ex: fa-newspaper, fa-film, fa-tower-broadcast</small>
        </div>

        <div class="form-group">
            <label for="sort_order">Ordre d'affichage</label>
            <input type="number" id="sort_order" name="sort_order" min="0" value="{{ old('sort_order', $product->sort_order) }}">
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true))>
                Afficher sur la page Nos produits
            </label>
        </div>

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

        <div class="form-actions" style="border: none; padding-top: 0; justify-content: flex-start;">
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
    </form>
@endsection
