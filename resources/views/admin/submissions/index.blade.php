@extends('admin.layouts.app')

@section('title', 'Soumissions journalistes')
@section('page-title', 'Soumissions de contenus')

@section('content')
    <div class="admin-panel">
        <form class="admin-filters" method="GET">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher…">
            <select name="status">
                <option value="">Tous les statuts</option>
                @foreach ($statuses as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Filtrer</button>
        </form>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Journaliste</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($submissions as $submission)
                    <tr>
                        <td>{{ $submission->title }}</td>
                        <td>{{ $submission->author?->name }}</td>
                        <td>{{ $submission->statusLabel() }}</td>
                        <td>{{ $submission->created_at->format('d/m/Y') }}</td>
                        <td><a href="{{ route('admin.submissions.show', $submission) }}" class="btn btn-secondary btn-sm">Examiner</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5">Aucune soumission.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $submissions->links() }}
    </div>
@endsection
