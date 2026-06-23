@extends('admin.layouts.app')

@section('title', $stat->exists ? 'Modifier la statistique' : 'Nouvelle statistique')
@section('page-title', $stat->exists ? 'Modifier la statistique' : 'Nouvelle statistique')

@section('content')
    <form
        class="admin-form"
        method="POST"
        action="{{ $stat->exists ? route('admin.site-stats.update', $stat) : route('admin.site-stats.store') }}"
        style="max-width: 520px;"
    >
        @csrf
        @if ($stat->exists)
            @method('PUT')
        @endif

        <div class="form-group">
            <label for="value">Valeur *</label>
            <input type="number" id="value" name="value" min="0" value="{{ old('value', $stat->value) }}" required>
        </div>

        <div class="form-group">
            <label for="label">Libellé *</label>
            <input type="text" id="label" name="label" value="{{ old('label', $stat->label) }}" placeholder="ex: Correspondants" required>
        </div>

        <div class="form-group">
            <label for="sort_order">Ordre d'affichage</label>
            <input type="number" id="sort_order" name="sort_order" min="0" value="{{ old('sort_order', $stat->sort_order) }}">
        </div>

        <div class="form-group">
            <label class="checkbox-label">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $stat->is_active ?? true))>
                Afficher sur l'accueil
            </label>
        </div>

        <div class="form-actions" style="border: none; padding-top: 0; justify-content: flex-start;">
            <a href="{{ route('admin.site-stats.index') }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
    </form>
@endsection
