@extends('admin.layouts.app')

@section('title', 'Relations presse')
@section('page-title', 'Page Relations presse')

@section('content')
    <form class="admin-form" method="POST" action="{{ route('admin.settings.press.update') }}" style="max-width: 720px;">
        @csrf
        @method('PUT')

        <p class="form-hint" style="margin-bottom: 20px;">Contenu affiché sur la page <a href="{{ route('press') }}" target="_blank">Relations presse</a>.</p>

        <div class="locale-block">
            <h3 class="locale-block-title"><i class="fa-solid fa-language" aria-hidden="true"></i> Français <span class="badge badge-published">Par défaut</span></h3>
            <div class="form-group">
                <label for="intro">Introduction *</label>
                <textarea id="intro" name="intro" rows="4" required>{{ old('intro', $intro) }}</textarea>
            </div>
            <div class="form-group">
                <label for="services">Services presse *</label>
                <textarea id="services" name="services" rows="6" required placeholder="Un service par ligne">{{ old('services', $servicesText) }}</textarea>
            </div>
        </div>

        <div class="locale-block">
            <h3 class="locale-block-title"><i class="fa-solid fa-language" aria-hidden="true"></i> English</h3>
            <div class="form-group">
                <label for="intro_en">Introduction</label>
                <textarea id="intro_en" name="intro_en" rows="4">{{ old('intro_en', $introEn) }}</textarea>
            </div>
            <div class="form-group">
                <label for="services_en">Press services</label>
                <textarea id="services_en" name="services_en" rows="6" placeholder="One service per line">{{ old('services_en', $servicesTextEn) }}</textarea>
            </div>
        </div>

        <div class="locale-block">
            <h3 class="locale-block-title"><i class="fa-solid fa-language" aria-hidden="true"></i> Português</h3>
            <div class="form-group">
                <label for="intro_pt">Introdução</label>
                <textarea id="intro_pt" name="intro_pt" rows="4">{{ old('intro_pt', $introPt) }}</textarea>
            </div>
            <div class="form-group">
                <label for="services_pt">Serviços de imprensa</label>
                <textarea id="services_pt" name="services_pt" rows="6" placeholder="Um serviço por linha">{{ old('services_pt', $servicesTextPt) }}</textarea>
            </div>
        </div>

        <div class="form-actions" style="border: none; padding-top: 0; justify-content: flex-start;">
            <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
    </form>
@endsection
