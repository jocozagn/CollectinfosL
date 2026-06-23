@extends('admin.layouts.app')

@section('title', 'Enquêtes')
@section('page-title', 'Enquêtes ouvertes')

@section('content')
    <div class="admin-panel admin-panel--data">
        <div class="panel-header">
            <h2>{{ $investigations->total() }} enquête(s)</h2>
            <div class="panel-header-actions">
                <a href="{{ route('admin.investigations.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus" aria-hidden="true"></i> Nouvelle enquête</a>
            </div>
        </div>

        @if ($investigations->isEmpty())
            <p class="empty-state">Aucune enquête. <a href="{{ route('admin.investigations.create') }}">Créer la première</a>.</p>
        @else
            <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Pays</th>
                        <th>Thème</th>
                        <th>Places</th>
                        <th>Statut</th>
                        <th>Journaliste</th>
                        <th>Publiée</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($investigations as $item)
                        <tr>
                            <td>{{ Str::limit($item->title, 45) }}</td>
                            <td>{{ $item->country ?? '—' }}</td>
                            <td>{{ $item->themeLabel() ?? '—' }}</td>
                            <td>{{ $item->places }}</td>
                            <td>
                                <span @class([
                                    'badge',
                                    'badge-open' => $item->status === 'open',
                                    'badge-closed' => $item->status === 'closed',
                                    'badge-pending' => $item->status === 'pending',
                                ])>
                                    {{ $item->statusLabel() }}
                                </span>
                            </td>
                            <td>{{ $item->owner?->name ?? '—' }}</td>
                            <td>{{ $item->published_at?->format('d/m/Y') ?? '—' }}</td>
                            <td class="actions">
                                <a href="{{ route('admin.investigations.edit', $item) }}" class="btn btn-sm btn-outline btn-icon" title="Modifier"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                <form action="{{ route('admin.investigations.destroy', $item) }}" method="POST" onsubmit="return confirm('Supprimer cette enquête ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger btn-icon" title="Supprimer"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            <div class="pagination-wrap">{{ $investigations->links() }}</div>
        @endif
    </div>
@endsection
