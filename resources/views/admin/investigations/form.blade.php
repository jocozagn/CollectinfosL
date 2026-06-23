@extends('admin.layouts.app')

@section('title', $investigation->exists ? 'Modifier l\'enquête' : 'Nouvelle enquête')
@section('page-title', $investigation->exists ? 'Modifier l\'enquête' : 'Nouvelle enquête')

@section('content')
    <form
        class="admin-form"
        method="POST"
        action="{{ $investigation->exists ? route('admin.investigations.update', $investigation) : route('admin.investigations.store') }}"
    >
        @csrf
        @if ($investigation->exists)
            @method('PUT')
        @endif

        <div class="form-grid">
            <div class="form-main">
                <div class="form-group">
                    <label for="title">Titre *</label>
                    <input type="text" id="title" name="title" value="{{ old('title', $investigation->title) }}" required>
                </div>

                <div class="form-group">
                    <label for="summary">Résumé *</label>
                    <textarea id="summary" name="summary" rows="6" required>{{ old('summary', $investigation->summary) }}</textarea>
                </div>
            </div>

            <div class="form-sidebar">
                <div class="sidebar-box">
                    <h3>Publication</h3>
                    <div class="form-group">
                        <label for="status">Statut *</label>
                        <select id="status" name="status" required>
                            <option value="open" @selected(old('status', $investigation->status) === 'open')>Ouverte</option>
                            <option value="closed" @selected(old('status', $investigation->status) === 'closed')>Fermée</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="places">Places disponibles *</label>
                        <input type="number" id="places" name="places" min="1" max="50" value="{{ old('places', $investigation->places) }}" required>
                    </div>
                </div>

                <div class="sidebar-box">
                    <h3>Classification</h3>
                    <div class="form-group">
                        <label for="country">Pays</label>
                        <input type="text" id="country" name="country" value="{{ old('country', $investigation->country) }}">
                    </div>
                    <div class="form-group">
                        <label for="theme">Thème</label>
                        <select id="theme" name="theme">
                            <option value="">— Choisir —</option>
                            @foreach ($themes as $key => $label)
                                <option value="{{ $key }}" @selected(old('theme', $investigation->theme) === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.investigations.index') }}" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-check" aria-hidden="true"></i>
                {{ $investigation->exists ? 'Enregistrer' : 'Créer l\'enquête' }}
            </button>
        </div>
    </form>
@endsection
