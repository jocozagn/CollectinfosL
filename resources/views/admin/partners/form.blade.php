@extends('admin.layouts.app')

@section('title', $partner->exists ? 'Modifier le partenaire' : 'Nouveau partenaire')
@section('page-title', $partner->exists ? 'Modifier le partenaire' : 'Nouveau partenaire')

@section('content')
    <form
        class="admin-form"
        method="POST"
        action="{{ $partner->exists ? route('admin.partners.update', $partner) : route('admin.partners.store') }}"
        enctype="multipart/form-data"
        style="max-width: 560px;"
    >
        @csrf
        @if ($partner->exists)
            @method('PUT')
        @endif

        <div class="form-group">
            <label for="name">Nom du média</label>
            <input type="text" id="name" name="name" value="{{ old('name', $partner->name) }}" placeholder="ex: RTG">
        </div>

        <div class="form-group">
            <label for="logo">Logo {{ $partner->exists ? '' : '*' }}</label>
            @if ($partner->logoUrl())
                <img src="{{ $partner->logoUrl() }}" alt="" class="current-media" style="max-width: 120px;">
            @endif
            <input type="file" id="logo" name="logo" accept="image/*">
        </div>

        <div class="form-group">
            <label for="logo_url">Ou URL du logo</label>
            <input type="url" id="logo_url" name="logo_url" value="{{ old('logo_url', str_starts_with($partner->logo ?? '', 'http') ? $partner->logo : '') }}" placeholder="https://...">
        </div>

        <div class="form-group">
            <label for="url">Site web (optionnel)</label>
            <input type="url" id="url" name="url" value="{{ old('url', $partner->url) }}" placeholder="https://...">
        </div>

        <div class="form-group">
            <label for="sort_order">Ordre d'affichage</label>
            <input type="number" id="sort_order" name="sort_order" min="0" value="{{ old('sort_order', $partner->sort_order) }}">
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $partner->is_active ?? true))>
                Afficher sur l'accueil
            </label>
        </div>

        <div class="form-actions" style="border: none; padding-top: 0; justify-content: flex-start;">
            <a href="{{ route('admin.partners.index') }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
    </form>
@endsection
