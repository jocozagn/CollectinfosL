@extends('layouts.app')

@section('title', $investigation->title.' – Espace collaboratif')

@section('content')
    <section class="page-hero page-hero--compact">
        <div class="container">
            <a href="{{ route('account', ['tab' => $investigation->isOwner($user) ? 'investigations' : 'participations']) }}" class="workspace-back-link">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour à mon compte
            </a>
            <h1 class="page-title">{{ $investigation->title }}</h1>
            <p class="page-subtitle">
                Espace collaboratif sécurisé — discutez, partagez vos fichiers et validez vos contenus sans quitter Collectinfos.
            </p>
            <div class="workspace-hero-meta">
                <span @class([
                    'workspace-hero-chip',
                    'workspace-hero-chip--status',
                    'workspace-hero-chip--open' => $investigation->status === 'open',
                    'workspace-hero-chip--closed' => $investigation->status === 'closed',
                    'workspace-hero-chip--pending' => $investigation->status === 'pending',
                ])>
                    <i class="fa-solid fa-circle-dot" aria-hidden="true"></i>
                    {{ $investigation->statusLabel() }}
                </span>
                <span class="workspace-hero-chip workspace-hero-chip--role">
                    <i class="fa-solid fa-id-badge" aria-hidden="true"></i>
                    {{ $userRole }}
                </span>
                @if ($investigation->country)
                    <span class="workspace-hero-chip workspace-hero-chip--location">
                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                        {{ $investigation->country }}
                    </span>
                @endif
            </div>
        </div>
    </section>

    <section class="section-page">
        <div class="container">
            @if (session('workspace_success'))
                <div class="ci-alert ci-alert--success" role="status">{{ session('workspace_success') }}</div>
            @endif

            @if ($errors->any())
                <div class="ci-alert ci-alert--error" role="alert">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="workspace-layout">
                <aside class="workspace-sidebar">
                    <div class="sidebar-box">
                        <h3>Résumé</h3>
                        <p>{{ $investigation->summary }}</p>
                    </div>
                    <div class="sidebar-box">
                        <h3>Équipe ({{ count($team) }})</h3>
                        <ul class="workspace-team-preview">
                            @foreach ($team as $member)
                                <li>
                                    <strong>{{ $member['user']->name }}</strong>
                                    <span>{{ $member['role_label'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </aside>

                <div class="workspace-main">
                    <nav class="workspace-tabs" aria-label="Sections de l'espace collaboratif">
                        <a href="{{ route('account.investigations.show', ['investigation' => $investigation->slug, 'section' => 'discussion']) }}"
                           @class(['workspace-tab', 'active' => $section === 'discussion'])>
                            <i class="fa-solid fa-comments" aria-hidden="true"></i> Discussion
                        </a>
                        <a href="{{ route('account.investigations.show', ['investigation' => $investigation->slug, 'section' => 'files']) }}"
                           @class(['workspace-tab', 'active' => $section === 'files'])>
                            <i class="fa-solid fa-folder-open" aria-hidden="true"></i> Fichiers
                            <span class="workspace-tab-count">{{ $files->count() }}</span>
                        </a>
                        <a href="{{ route('account.investigations.show', ['investigation' => $investigation->slug, 'section' => 'contents']) }}"
                           @class(['workspace-tab', 'active' => $section === 'contents'])>
                            <i class="fa-solid fa-file-lines" aria-hidden="true"></i> Contenus
                            <span class="workspace-tab-count">{{ $drafts->count() }}</span>
                        </a>
                        <a href="{{ route('account.investigations.show', ['investigation' => $investigation->slug, 'section' => 'team']) }}"
                           @class(['workspace-tab', 'active' => $section === 'team'])>
                            <i class="fa-solid fa-users" aria-hidden="true"></i> Équipe
                        </a>
                        @if ($isOwner)
                            <a href="{{ route('account.investigations.show', ['investigation' => $investigation->slug, 'section' => 'candidatures']) }}"
                               @class(['workspace-tab', 'active' => $section === 'candidatures'])>
                                <i class="fa-solid fa-user-plus" aria-hidden="true"></i> Candidatures
                                @if ($pendingCandidatures->isNotEmpty())
                                    <span class="workspace-tab-count workspace-tab-count--alert">{{ $pendingCandidatures->count() }}</span>
                                @endif
                            </a>
                        @endif
                    </nav>

                    <div class="workspace-panel">
                        @if ($section === 'discussion')
                            <div class="workspace-chat" id="workspace-chat"
                                 data-messages-url="{{ route('account.investigations.messages', $investigation) }}"
                                 data-last-id="{{ $messages->last()?->id ?? 0 }}">
                                <div class="workspace-chat-messages" id="workspace-chat-messages" aria-live="polite">
                                    @forelse ($messages as $message)
                                        <article @class(['workspace-message', 'workspace-message--mine' => $message->user_id === $user->id]) data-message-id="{{ $message->id }}">
                                            <header>
                                                <strong>{{ $message->user->name }}</strong>
                                                <time datetime="{{ $message->created_at->toIso8601String() }}">{{ $message->created_at->format('d/m/Y H:i') }}</time>
                                            </header>
                                            <p>{!! nl2br(e($message->body)) !!}</p>
                                        </article>
                                    @empty
                                        <p class="workspace-empty" id="workspace-chat-empty">Aucun message pour le moment. Lancez la discussion avec votre équipe.</p>
                                    @endforelse
                                </div>

                                @if ($canContribute)
                                    <form class="workspace-chat-form ci-form" method="POST"
                                          action="{{ route('account.investigations.messages.store', $investigation) }}"
                                          id="workspace-chat-form">
                                        @csrf
                                        <div class="form-group">
                                            <label for="message-body" class="sr-only">Votre message</label>
                                            <textarea id="message-body" name="body" rows="3" placeholder="Écrivez votre message à l'équipe…" required maxlength="5000">{{ old('body') }}</textarea>
                                        </div>
                                        <button type="submit" class="ci-btn ci-btn--primary">
                                            <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Envoyer
                                        </button>
                                    </form>
                                @else
                                    <p class="workspace-readonly-note"><i class="fa-solid fa-eye" aria-hidden="true"></i> Vous avez un accès en lecture seule sur cette enquête.</p>
                                @endif
                            </div>

                        @elseif ($section === 'files')
                            <div class="workspace-files">
                                @if ($canContribute)
                                    <div class="workspace-upload-box">
                                        <h3><i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i> Partager un fichier</h3>
                                        <form class="ci-form" method="POST" action="{{ route('account.investigations.files.store', $investigation) }}" enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-group">
                                                <label for="workspace-file">Fichier *</label>
                                                <input type="file" id="workspace-file" name="file" required
                                                       data-upload-hint="Documents, photos, audio, vidéo — max. 50 Mo">
                                            </div>
                                            <div class="form-group">
                                                <label for="file-description">Description (optionnel)</label>
                                                <input type="text" id="file-description" name="description" value="{{ old('description') }}" maxlength="500" placeholder="Ex. : interview terrain, notes de rédaction…">
                                            </div>
                                            <button type="submit" class="ci-btn ci-btn--primary">
                                                <i class="fa-solid fa-upload" aria-hidden="true"></i> Envoyer le fichier
                                            </button>
                                        </form>
                                    </div>
                                @endif

                                @if ($files->isEmpty())
                                    <p class="workspace-empty">Aucun fichier partagé pour le moment.</p>
                                @else
                                    <div class="workspace-file-list">
                                        @foreach ($files as $file)
                                            <article class="workspace-file-card">
                                                <div class="workspace-file-icon">
                                                    <i class="fa-solid fa-file" aria-hidden="true"></i>
                                                </div>
                                                <div class="workspace-file-info">
                                                    <h4>{{ $file->original_name }}</h4>
                                                    @if ($file->description)
                                                        <p>{{ $file->description }}</p>
                                                    @endif
                                                    <p class="workspace-file-meta">
                                                        {{ $file->user->name }} · {{ $file->created_at->format('d/m/Y H:i') }} · {{ $file->humanSize() }}
                                                    </p>
                                                </div>
                                                <div class="workspace-file-actions">
                                                    <a href="{{ route('account.investigations.files.download', [$investigation, $file]) }}" class="ci-btn ci-btn--outline ci-btn--sm">
                                                        <i class="fa-solid fa-download" aria-hidden="true"></i> Télécharger
                                                    </a>
                                                    @if ($canContribute && ($file->user_id === $user->id || $canManageTeam))
                                                        <form method="POST" action="{{ route('account.investigations.files.destroy', [$investigation, $file]) }}" onsubmit="return confirm('Supprimer ce fichier ?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="ci-btn ci-btn--outline ci-btn--sm workspace-btn-danger">
                                                                <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                        @elseif ($section === 'contents')
                            <div class="workspace-contents">
                                @if ($canContribute)
                                    <div class="workspace-upload-box">
                                        <h3><i class="fa-solid fa-pen" aria-hidden="true"></i> Rédiger un contenu</h3>
                                        <form class="ci-form" method="POST" action="{{ route('account.investigations.drafts.store', $investigation) }}">
                                            @csrf
                                            <div class="form-group">
                                                <label for="draft-title">Titre *</label>
                                                <input type="text" id="draft-title" name="title" value="{{ old('title') }}" required maxlength="255">
                                            </div>
                                            <div class="form-group">
                                                <label for="draft-body">Texte / script / article *</label>
                                                <textarea id="draft-body" name="body" rows="8" required maxlength="50000">{{ old('body') }}</textarea>
                                            </div>
                                            <div class="workspace-form-actions">
                                                <button type="submit" name="submit" value="0" class="ci-btn ci-btn--outline">
                                                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Enregistrer brouillon
                                                </button>
                                                <button type="submit" name="submit" value="1" class="ci-btn ci-btn--primary">
                                                    <i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Soumettre pour validation
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif

                                @if ($drafts->isEmpty())
                                    <p class="workspace-empty">Aucun contenu rédigé pour le moment.</p>
                                @else
                                    <div class="workspace-draft-list">
                                        @foreach ($drafts as $draft)
                                            <article class="workspace-draft-card">
                                                <div class="workspace-draft-head">
                                                    <h4>{{ $draft->title }}</h4>
                                                    <span @class([
                                                        'status-pill',
                                                        'status-pill--open' => $draft->status === 'approved',
                                                        'status-pill--pending' => $draft->status === 'pending_review',
                                                        'status-pill--closed' => $draft->status === 'rejected',
                                                    ])>{{ $draft->statusLabel() }}</span>
                                                </div>
                                                <p class="workspace-draft-meta">
                                                    Par {{ $draft->author->name }}
                                                    @if ($draft->submitted_at)
                                                        · Soumis le {{ $draft->submitted_at->format('d/m/Y H:i') }}
                                                    @endif
                                                    @if ($draft->reviewer)
                                                        · Validé par {{ $draft->reviewer->name }}
                                                    @endif
                                                </p>
                                                <div class="workspace-draft-body">{!! nl2br(e(Str::limit($draft->body, 600))) !!}</div>

                                                @if ($draft->review_note)
                                                    <div class="workspace-review-note">
                                                        <strong>Note de validation :</strong> {{ $draft->review_note }}
                                                    </div>
                                                @endif

                                                @if ($draft->isEditableBy($user))
                                                    <details class="workspace-draft-edit">
                                                        <summary>Modifier ce brouillon</summary>
                                                        <form class="ci-form" method="POST" action="{{ route('account.investigations.drafts.store', $investigation) }}">
                                                            @csrf
                                                            <input type="hidden" name="draft_id" value="{{ $draft->id }}">
                                                            <div class="form-group">
                                                                <label>Titre</label>
                                                                <input type="text" name="title" value="{{ $draft->title }}" required maxlength="255">
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Texte</label>
                                                                <textarea name="body" rows="6" required maxlength="50000">{{ $draft->body }}</textarea>
                                                            </div>
                                                            <div class="workspace-form-actions">
                                                                <button type="submit" name="submit" value="0" class="ci-btn ci-btn--outline ci-btn--sm">Enregistrer</button>
                                                                <button type="submit" name="submit" value="1" class="ci-btn ci-btn--primary ci-btn--sm">Resoumettre</button>
                                                            </div>
                                                        </form>
                                                    </details>
                                                @endif

                                                @if ($canReviewDrafts && $draft->status === 'pending_review')
                                                    <div class="workspace-review-actions">
                                                        <form method="POST" action="{{ route('account.investigations.drafts.review', [$investigation, $draft]) }}" class="workspace-review-form">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" name="status" value="approved">
                                                            <button type="submit" class="ci-btn ci-btn--primary ci-btn--sm">
                                                                <i class="fa-solid fa-check" aria-hidden="true"></i> Valider / co-signer
                                                            </button>
                                                        </form>
                                                        <form method="POST" action="{{ route('account.investigations.drafts.review', [$investigation, $draft]) }}" class="workspace-review-form ci-form">
                                                            @csrf
                                                            @method('PUT')
                                                            <input type="hidden" name="status" value="rejected">
                                                            <div class="form-group">
                                                                <label for="review-note-{{ $draft->id }}">Motif du retour (optionnel)</label>
                                                                <input type="text" id="review-note-{{ $draft->id }}" name="review_note" maxlength="2000" placeholder="Précisez ce qui doit être retravaillé…">
                                                            </div>
                                                            <button type="submit" class="ci-btn ci-btn--outline ci-btn--sm workspace-btn-danger">
                                                                <i class="fa-solid fa-rotate-left" aria-hidden="true"></i> Renvoyer pour révision
                                                            </button>
                                                        </form>
                                                    </div>
                                                @endif
                                            </article>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                        @elseif ($section === 'team')
                            <div class="workspace-team">
                                <p class="form-intro">Les rôles définissent ce que chaque membre peut faire dans cet espace.</p>
                                <ul class="workspace-team-roles-help">
                                    <li><strong>Porteur</strong> — crée l'enquête, gère l'équipe et valide les contenus.</li>
                                    <li><strong>Coordinateur</strong> — peut gérer les rôles et valider les contenus.</li>
                                    <li><strong>Contributeur</strong> — peut discuter, partager des fichiers et rédiger.</li>
                                    <li><strong>Lecteur</strong> — consultation seule (discussion et fichiers).</li>
                                </ul>

                                <div class="workspace-team-list">
                                    @foreach ($team as $member)
                                        <article class="workspace-team-card">
                                            <div>
                                                <h4>{{ $member['user']->name }}</h4>
                                                <p>{{ $member['user']->email }}</p>
                                                <p class="workspace-file-meta">
                                                    {{ $member['role_label'] }}
                                                    @if ($member['joined_at'])
                                                        · Depuis le {{ $member['joined_at']->format('d/m/Y') }}
                                                    @endif
                                                </p>
                                            </div>

                                            @if ($canManageTeam && $member['role'] !== 'owner' && $member['user']->id !== $user->id)
                                                <form method="POST" action="{{ route('account.investigations.team.role', [$investigation, $member['user']]) }}" class="workspace-role-form">
                                                    @csrf
                                                    @method('PUT')
                                                    <label class="sr-only" for="role-{{ $member['user']->id }}">Rôle</label>
                                                    <select id="role-{{ $member['user']->id }}" name="role" onchange="this.form.submit()">
                                                        <option value="lead" @selected($member['role'] === 'lead')>Coordinateur</option>
                                                        <option value="contributor" @selected($member['role'] === 'contributor')>Contributeur</option>
                                                        <option value="viewer" @selected($member['role'] === 'viewer')>Lecteur</option>
                                                    </select>
                                                </form>
                                            @endif
                                        </article>
                                    @endforeach
                                </div>
                            </div>

                        @elseif ($section === 'candidatures')
                            <div class="workspace-candidatures">
                                <p class="form-intro">
                                    Gérez les journalistes qui souhaitent rejoindre votre enquête.
                                    <strong>{{ $investigation->remainingPlaces() }}</strong> place(s) encore disponible(s) sur {{ $investigation->places }}.
                                </p>

                                @if ($pendingCandidatures->isEmpty())
                                    <p class="workspace-empty">Aucune candidature en attente pour le moment.</p>
                                @else
                                    <div class="workspace-candidature-list">
                                        @foreach ($pendingCandidatures as $candidature)
                                            <article class="workspace-candidature-card">
                                                <div class="workspace-candidature-main">
                                                    <h4>{{ $candidature->name }}</h4>
                                                    <p class="workspace-file-meta">
                                                        <a href="mailto:{{ $candidature->email }}">{{ $candidature->email }}</a>
                                                        @if ($candidature->phone)
                                                            · {{ $candidature->phone }}
                                                        @endif
                                                        @if ($candidature->country)
                                                            · {{ $candidature->country }}
                                                        @endif
                                                    </p>
                                                    <p class="workspace-candidature-date">
                                                        Candidature reçue le {{ $candidature->created_at->format('d/m/Y à H:i') }}
                                                    </p>
                                                    <div class="workspace-candidature-message">
                                                        {!! nl2br(e($candidature->message)) !!}
                                                    </div>
                                                </div>
                                                <div class="workspace-candidature-actions">
                                                    <form method="POST" action="{{ route('account.investigations.candidatures.update', [$investigation, $candidature]) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="status" value="accepted">
                                                        <button type="submit" class="ci-btn ci-btn--primary ci-btn--sm" @disabled(! $investigation->hasAvailablePlace())>
                                                            <i class="fa-solid fa-check" aria-hidden="true"></i> Accepter
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('account.investigations.candidatures.update', [$investigation, $candidature]) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="status" value="rejected">
                                                        <button type="submit" class="ci-btn ci-btn--outline ci-btn--sm workspace-btn-danger">
                                                            <i class="fa-solid fa-xmark" aria-hidden="true"></i> Refuser
                                                        </button>
                                                    </form>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        window.CollectinfosWorkspace = {
            section: @json($section),
            messagesUrl: @json(route('account.investigations.messages', $investigation)),
            streamUrl: @json(route('account.investigations.messages.stream', $investigation)),
            storeMessageUrl: @json(route('account.investigations.messages.store', $investigation)),
            currentUserId: @json($user->id),
        };
    </script>
    <script src="{{ asset('js/investigation-workspace.js') }}?v=3"></script>
@endpush
