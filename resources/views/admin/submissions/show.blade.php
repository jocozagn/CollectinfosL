@extends('admin.layouts.app')

@section('title', 'Modération soumission')
@section('page-title', 'Modération : '.$submission->title)

@section('content')
    <div class="admin-panel">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="detail-grid">
            <div class="detail-item"><label>Journaliste</label><p>{{ $submission->author?->name }} ({{ $submission->author?->email }})</p></div>
            <div class="detail-item"><label>Statut</label><p>{{ $submission->statusLabel() }}</p></div>
            <div class="detail-item"><label>Type</label><p>{{ $submission->type }}</p></div>
            <div class="detail-item"><label>Pays</label><p>{{ $submission->country ?? '—' }}</p></div>
            <div class="detail-item detail-item--full"><label>Pitch</label><p>{{ $submission->summary }}</p></div>
            <div class="detail-item"><label>Accès</label><p>{{ $submission->access }} @if($submission->price)· {{ number_format($submission->price, 0) }} €@endif</p></div>
            @if ($submission->thumbnailUrl())
                <div class="detail-item"><label>Aperçu</label><p><img src="{{ $submission->thumbnailUrl() }}" alt="" style="max-width:200px;border-radius:8px;"></p></div>
            @endif
        </div>

        <form class="admin-form" method="POST" action="{{ route('admin.submissions.update', $submission) }}">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="review_note">Note de modération</label>
                <textarea id="review_note" name="review_note" rows="3">{{ old('review_note', $submission->review_note) }}</textarea>
            </div>
            <div class="form-actions">
                <a href="{{ route('admin.submissions.index') }}" class="btn btn-secondary">Retour</a>
                <button type="submit" name="action" value="review" class="btn btn-secondary">Passer en modération</button>
                <button type="submit" name="action" value="approve" class="btn btn-primary">Valider (brouillon)</button>
                <button type="submit" name="action" value="publish" class="btn btn-primary">Valider et publier</button>
                <button type="submit" name="action" value="reject" class="btn btn-danger" onclick="return confirm('Rejeter cette soumission ?')">Rejeter</button>
            </div>
        </form>
    </div>
@endsection
