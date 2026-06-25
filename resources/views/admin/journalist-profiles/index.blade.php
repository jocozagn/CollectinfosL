@extends('admin.layouts.app')

@section('title', 'Profils journalistes')
@section('page-title', 'Validation des profils journalistes')

@section('content')
    <div class="admin-panel">
        <form class="admin-filters" method="GET">
            <select name="verified">
                <option value="">Tous</option>
                <option value="1" @selected(request('verified') === '1')>Validés</option>
                <option value="0" @selected(request('verified') === '0')>Non validés</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Filtrer</button>
        </form>

        <table class="admin-table">
            <thead>
                <tr>
                    <th>Journaliste</th>
                    <th>Type</th>
                    <th>Complétion</th>
                    <th>Statut</th>
                    <th>Profil public</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($journalists as $journalist)
                    @php $completion = $profiles->completion($journalist); @endphp
                    <tr>
                        <td>{{ $journalist->name }}<br><small>{{ $journalist->email }}</small></td>
                        <td>{{ $journalist->accountTypeLabel() }}</td>
                        <td>{{ $completion['percent'] }}%</td>
                        <td>
                            @if ($journalist->isProfileVerified())
                                <span class="status-chip status-chip--approved">Validé</span>
                            @else
                                <span class="status-chip status-chip--pending">En attente</span>
                            @endif
                        </td>
                        <td>
                            @if ($journalist->publicProfileUrl())
                                <a href="{{ $journalist->publicProfileUrl() }}" target="_blank" rel="noopener">Voir</a>
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            @if ($journalist->isProfileVerified())
                                <form method="POST" action="{{ route('admin.journalist-profiles.unverify', $journalist) }}" class="inline-form">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-secondary btn-sm">Retirer</button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('admin.journalist-profiles.verify', $journalist) }}" class="inline-form">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-primary btn-sm">Valider</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Aucun journaliste inscrit.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $journalists->links() }}
    </div>
@endsection
