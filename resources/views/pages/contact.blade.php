@extends('layouts.app')

@section('title', 'Contact – Collectinfos')

@section('content')
    <section class="page-hero page-hero--compact">
        <div class="container">
            <h1 class="page-title">Contactez-nous</h1>
            <p class="page-subtitle">Une question, un partenariat ou une demande d'information ? Écrivez-nous.</p>
        </div>
    </section>

    <section class="section-page">
        <div class="container">
            <div class="page-grid">
                <div class="page-info">
                    <div class="info-card">
                        <i class="fa-solid fa-phone" aria-hidden="true"></i>
                        <h3>Téléphone</h3>
                        <p><a href="tel:{{ preg_replace('/\s+/', '', $contact['phone']) }}">{{ $contact['phone'] }}</a></p>
                    </div>
                    <div class="info-card">
                        <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                        <h3>E-mail</h3>
                        <p><a href="mailto:{{ $contact['email'] }}">{{ $contact['email'] }}</a></p>
                    </div>
                    <div class="info-card">
                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                        <h3>Zone d'action</h3>
                        <p>{{ $contact['zone'] ?? "Afrique de l'Ouest et panafrique — réseau de correspondants dans 18 pays." }}</p>
                    </div>
                </div>

                <div class="page-form-wrap" id="contact-form">
                    <h2 class="form-heading"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Envoyer un message</h2>

                    @if (session('contact_success'))
                        <div class="form-alert form-alert--success" role="status">
                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                            {{ session('contact_success') }}
                        </div>
                    @endif

                    <form class="ci-form" method="POST" action="{{ route('contact.store') }}">
                        @csrf
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Nom *</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                            </div>
                            <div class="form-group">
                                <label for="email">E-mail *</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="subject">Sujet</label>
                            <input type="text" id="subject" name="subject" value="{{ old('subject') }}" placeholder="Objet de votre message">
                        </div>
                        <div class="form-group">
                            <label for="message">Message *</label>
                            <textarea id="message" name="message" rows="6" required placeholder="Votre message…">{{ old('message') }}</textarea>
                        </div>
                        <button type="submit" class="ci-btn ci-btn--primary">
                            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Envoyer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
