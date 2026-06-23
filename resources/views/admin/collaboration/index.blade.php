@extends('admin.layouts.app')

@section('title', 'Candidatures collaboration')
@section('page-title', 'Candidatures collaboration')

@section('content')
    <div class="admin-panel">
        <div class="panel-header">
            <form class="filter-form" method="GET">
                <select name="type">
                    <option value="">Tous les types</option>
                    <option value="join" @selected(request('type') === 'join')>Rejoindre une enquête</option>
                    <option value="propose" @selected(request('type') === 'propose')>Proposer une enquête</option>
                </select>
                <select name="status">
                    <option value="">Tous les statuts</option>
                    <option value="pending" @selected(request('status') === 'pending')>En attente</option>
                    <option value="accepted" @selected(request('status') === 'accepted')>Acceptée</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Refusée</option>
                </select>
                <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-filter" aria-hidden="true"></i> Filtrer</button>
            </form>
        </div>

        @if ($requests->isEmpty())
            <p class="empty-state">Aucune candidature trouvée.</p>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Nom</th>
                        <th>Type</th>
                        <th>Enquête</th>
                        <th>Statut</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $item)
                        <tr>
                            <td>{{ $item->created_at->format('d/m/Y') }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->type === 'join' ? 'Rejoindre' : 'Proposer' }}</td>
                            <td>{{ $item->investigation?->title ?? '—' }}</td>
                            <td>
                                <span @class([
                                    'badge',
                                    'badge-pending' => $item->status === 'pending',
                                    'badge-accepted' => $item->status === 'accepted',
                                    'badge-rejected' => $item->status === 'rejected',
                                ])>
                                    @switch($item->status)
                                        @case('accepted') Acceptée @break
                                        @case('rejected') Refusée @break
                                        @default En attente
                                    @endswitch
                                </span>
                            </td>
                            <td><a href="{{ route('admin.collaboration.show', $item) }}" class="btn btn-sm"><i class="fa-solid fa-eye" aria-hidden="true"></i> Voir</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pagination-wrap">{{ $requests->links() }}</div>
        @endif
    </div>
@endsection
