@extends('admin.layouts.app')

@section('title', 'Coordonnées du site')
@section('page-title', 'Coordonnées & contact')

@section('content')
    <form class="admin-form" method="POST" action="{{ route('admin.settings.contact.update') }}" style="max-width: 560px;">
        @csrf
        @method('PUT')

        <p class="form-hint" style="margin-bottom: 20px;">Ces informations s'affichent dans l'en-tête du site, sur la page Contact et Relations presse.</p>

        <div class="form-group">
            <label for="phone">Téléphone *</label>
            <input type="text" id="phone" name="phone" value="{{ old('phone', $contact['phone']) }}" required>
        </div>

        <div class="form-group">
            <label for="email">E-mail *</label>
            <input type="email" id="email" name="email" value="{{ old('email', $contact['email']) }}" required>
        </div>

        <div class="form-group">
            <label for="zone">Zone d'action</label>
            <textarea id="zone" name="zone" rows="3">{{ old('zone', $contact['zone']) }}</textarea>
        </div>

        <div class="form-actions" style="border: none; padding-top: 0; justify-content: flex-start;">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
    </form>
@endsection
