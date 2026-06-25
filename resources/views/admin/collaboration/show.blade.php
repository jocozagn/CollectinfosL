@extends('admin.layouts.app')

@section('title', 'Candidature collaboration')
@section('page-title', 'Candidature collaboration')

@section('content')
    <div class="admin-panel">
        <div class="panel-header">
            <h2>{{ $request->name }}</h2>
            <div class="panel-actions">
                <a href="{{ route('admin.collaboration.index') }}" class="btn btn-secondary"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour</a>
                <a href="mailto:{{ $request->email }}" class="btn btn-primary"><i class="fa-solid fa-envelope" aria-hidden="true"></i> Contacter</a>
            </div>
        </div>

        <div class="detail-grid">
            <div class="detail-item">
                <label>Type</label>
                <p>{{ $request->typeLabel() }}</p>
            </div>
            @if ($request->proposed_title)
            <div class="detail-item">
                <label>Titre proposé</label>
                <p>{{ $request->proposed_title }}</p>
            </div>
            @endif
            @if ($request->user)
            <div class="detail-item">
                <label>Compte journaliste</label>
                <p>{{ $request->user->name }} ({{ $request->user->email }})</p>
            </div>
            @endif
            <div class="detail-item">
                <label>Email</label>
                <p><a href="mailto:{{ $request->email }}">{{ $request->email }}</a></p>
            </div>
            <div class="detail-item">
                <label>Téléphone</label>
                <p>{{ $request->phone ?? '—' }}</p>
            </div>
            <div class="detail-item">
                <label>Pays</label>
                <p>{{ $request->country ?? '—' }}</p>
            </div>
            <div class="detail-item">
                <label>Enquête</label>
                <p>
                    @if ($request->investigation)
                        {{ $request->investigation->title }}
                        @if ($request->status === 'accepted')
                            <br><small>Les participants peuvent collaborer via l'espace sécurisé de l'enquête (messagerie, fichiers, validation des contenus).</small>
                        @endif
                    @else
                        —
                    @endif
                </p>
            </div>
            <div class="detail-item">
                <label>Reçue le</label>
                <p>{{ $request->created_at->format('d/m/Y à H:i') }}</p>
            </div>
        </div>

        <h3 style="margin: 0 0 12px; font-size: 14px; text-transform: uppercase; color: #666;">Message</h3>
        <div class="message-body" style="margin-bottom: 24px;">{{ $request->message }}</div>

        <form class="admin-form" method="POST" action="{{ route('admin.collaboration.update', $request) }}" style="max-width: 400px;">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="status">Statut de la candidature</label>
                <select id="status" name="status" required>
                    <option value="pending" @selected(old('status', $request->status) === 'pending')>En attente</option>
                    <option value="accepted" @selected(old('status', $request->status) === 'accepted')>Acceptée</option>
                    <option value="rejected" @selected(old('status', $request->status) === 'rejected')>Refusée</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check" aria-hidden="true"></i> Enregistrer</button>
        </form>

        <form action="{{ route('admin.collaboration.destroy', $request) }}" method="POST" style="margin-top: 16px;" onsubmit="return confirm('Supprimer cette candidature ?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash" aria-hidden="true"></i> Supprimer la candidature</button>
        </form>
    </div>
@endsection
