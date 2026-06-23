@extends('admin.layouts.app')

@section('title', 'Enquêtes')
@section('page-title', 'Enquêtes ouvertes')

@section('content')
    <div class="admin-panel">
        <div class="panel-header">
            <h2>{{ $investigations->total() }} enquête(s)</h2>
            <a href="{{ route('admin.investigations.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus" aria-hidden="true"></i> Nouvelle enquête</a>
        </div>

        @if ($investigations->isEmpty())
            <p class="empty-state">Aucune enquête. <a href="{{ route('admin.investigations.create') }}">Créer la première</a>.</p>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Pays</th>
                        <th>Thème</th>
                        <th>Places</th>
                        <th>Statut</th>
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
                                <span @class(['badge', 'badge-open' => $item->status === 'open', 'badge-closed' => $item->status === 'closed'])>
                                    {{ $item->status === 'open' ? 'Ouverte' : 'Fermée' }}
                                </span>
                            </td>
                            <td>{{ $item->published_at?->format('d/m/Y') ?? '—' }}</td>
                            <td class="actions">
                                <a href="{{ route('admin.investigations.edit', $item) }}" class="btn btn-sm"><i class="fa-solid fa-pen" aria-hidden="true"></i> Modifier</a>
                                <form action="{{ route('admin.investigations.destroy', $item) }}" method="POST" onsubmit="return confirm('Supprimer cette enquête ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pagination-wrap">{{ $investigations->links() }}</div>
        @endif
    </div>
@endsection
