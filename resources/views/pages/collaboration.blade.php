@extends('layouts.app')

@section('title', 'Collaboration – Collectinfos')

@section('content')
    <section class="page-hero page-hero--compact">
        <div class="container">
            <h1 class="page-title">Espace Collaboration</h1>
            <p class="page-subtitle">Proposez une enquête ou rejoignez un projet journalistique avec le réseau Collectinfos.</p>
        </div>
    </section>

    <section class="section-page">
        <div class="container">
            <div class="collab-steps">
                <div class="step-card">
                    <span class="step-num">1</span>
                    <i class="fa-solid fa-lightbulb" aria-hidden="true"></i>
                    <h3>Proposez</h3>
                    <p>Soumettez votre idée d'enquête ou de reportage.</p>
                </div>
                <div class="step-card">
                    <span class="step-num">2</span>
                    <i class="fa-solid fa-users" aria-hidden="true"></i>
                    <h3>Collaborez</h3>
                    <p>Rejoignez une enquête ouverte et travaillez en équipe.</p>
                </div>
                <div class="step-card">
                    <span class="step-num">3</span>
                    <i class="fa-solid fa-globe-africa" aria-hidden="true"></i>
                    <h3>Publiez</h3>
                    <p>Diffusez vos productions via Collectinfos et nos partenaires.</p>
                </div>
            </div>

            <div class="section-title">
                <h4>ENQUÊTES OUVERTES</h4>
            </div>

            @if ($investigations->isEmpty())
                <p class="empty-text">Aucune enquête ouverte pour le moment. Proposez la vôtre ci-dessous.</p>
            @else
                <div class="investigations-grid">
                    @foreach ($investigations as $item)
                        <article class="investigation-card">
                            <div class="investigation-meta">
                                @if ($item->country)
                                    <span><i class="fa-solid fa-location-dot" aria-hidden="true"></i> {{ $item->country }}</span>
                                @endif
                                @if ($item->theme)
                                    <span><i class="fa-solid fa-tag" aria-hidden="true"></i> {{ $item->themeLabel() }}</span>
                                @endif
                                <span><i class="fa-solid fa-user-group" aria-hidden="true"></i> {{ $item->places }} place(s)</span>
                            </div>
                            <h3>{{ $item->title }}</h3>
                            <p>{{ $item->summary }}</p>
                            <button type="button" class="ci-btn ci-btn--outline btn-join-investigation" data-id="{{ $item->id }}" data-title="{{ e($item->title) }}">
                                <i class="fa-solid fa-handshake" aria-hidden="true"></i> Rejoindre
                            </button>
                        </article>
                    @endforeach
                </div>
            @endif

            <div class="page-form-wrap page-form-wrap--wide" id="collaboration-form">
                <h2 class="form-heading"><i class="fa-solid fa-pen-to-square" aria-hidden="true"></i> Votre candidature</h2>

                @if (session('collaboration_success'))
                    <div class="form-alert form-alert--success" role="status">
                        <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                        {{ session('collaboration_success') }}
                    </div>
                @endif

                <form class="ci-form" method="POST" action="{{ route('collaboration.store') }}" id="collaboration-form-el">
                    @csrf
                    <div class="form-group">
                        <label>Type de demande *</label>
                        <div class="radio-group">
                            <label class="radio-label">
                                <input type="radio" name="type" value="propose" @checked(old('type', 'propose') === 'propose')>
                                <span><i class="fa-solid fa-lightbulb" aria-hidden="true"></i> Proposer une enquête</span>
                            </label>
                            <label class="radio-label">
                                <input type="radio" name="type" value="join" @checked(old('type') === 'join')>
                                <span><i class="fa-solid fa-handshake" aria-hidden="true"></i> Rejoindre une enquête</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group" id="investigation-select-wrap">
                        <label for="investigation_id">Enquête *</label>
                        <select id="investigation_id" name="investigation_id">
                            <option value="">— Choisir une enquête —</option>
                            @foreach ($investigations as $item)
                                <option value="{{ $item->id }}" @selected(old('investigation_id') == $item->id)>{{ $item->title }}</option>
                            @endforeach
                        </select>
                        @error('investigation_id')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="collab-name">Nom *</label>
                            <input type="text" id="collab-name" name="name" value="{{ old('name') }}" required>
                        </div>
                        <div class="form-group">
                            <label for="collab-email">E-mail *</label>
                            <input type="email" id="collab-email" name="email" value="{{ old('email') }}" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="collab-phone">Téléphone</label>
                            <input type="text" id="collab-phone" name="phone" value="{{ old('phone') }}">
                        </div>
                        <div class="form-group">
                            <label for="collab-country">Pays</label>
                            <input type="text" id="collab-country" name="country" value="{{ old('country') }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="collab-message">Message *</label>
                        <textarea id="collab-message" name="message" rows="5" required placeholder="Décrivez votre projet ou votre motivation…">{{ old('message') }}</textarea>
                    </div>
                    <button type="submit" class="ci-btn ci-btn--primary">
                        <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Envoyer ma candidature
                    </button>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const selectWrap = document.getElementById('investigation-select-wrap');
    const select = document.getElementById('investigation_id');

    function toggleInvestigation() {
        const isJoin = document.querySelector('input[name="type"]:checked')?.value === 'join';
        selectWrap.style.display = isJoin ? 'block' : 'none';
        if (!isJoin) select.value = '';
    }

    typeRadios.forEach(function (r) { r.addEventListener('change', toggleInvestigation); });
    toggleInvestigation();

    document.querySelectorAll('.btn-join-investigation').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelector('input[name="type"][value="join"]').checked = true;
            toggleInvestigation();
            select.value = btn.dataset.id;
            document.getElementById('collaboration-form').scrollIntoView({ behavior: 'smooth' });
        });
    });
});
</script>
@endpush
