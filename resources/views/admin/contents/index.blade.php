@extends('admin.layouts.app')

@section('title', 'Gestion des contenus')
@section('page-title', 'Gestion des contenus')

@section('content')
    <div class="admin-panel admin-panel--data">
        <div class="panel-header panel-header--toolbar">
            <form class="filter-form" method="GET">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher…">
                <select name="type">
                    <option value="">Tous les types</option>
                    @foreach ($types as $key => $label)
                        <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status">
                    <option value="">Tous les statuts</option>
                    <option value="published" @selected(request('status') === 'published')>Publié</option>
                    <option value="draft" @selected(request('status') === 'draft')>Brouillon</option>
                </select>
                <button type="submit" class="btn btn-secondary btn-sm"><i class="fa-solid fa-filter" aria-hidden="true"></i> Filtrer</button>
            </form>
            <div class="panel-header-actions">
                <a href="{{ route('admin.contents.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus" aria-hidden="true"></i> Nouveau contenu</a>
            </div>
        </div>

        @if ($contents->isEmpty())
            <p class="empty-state">Aucun contenu trouvé.</p>
        @else
            <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Vignette</th>
                        <th>Titre</th>
                        <th>Type</th>
                        <th>Pays</th>
                        <th>Accès</th>
                        <th>Prix</th>
                        <th>Exclusivité</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($contents as $item)
                        <tr>
                            <td>
                                @if ($item->thumbnailUrl())
                                    <img src="{{ $item->thumbnailUrl() }}" alt="" class="thumb-preview">
                                @else
                                    <span class="thumb-placeholder">—</span>
                                @endif
                            </td>
                            <td>{{ Str::limit($item->title, 45) }}</td>
                            <td>{{ $types[$item->type] ?? $item->type }}</td>
                            <td>{{ $item->country ?? '—' }}</td>
                            <td>{{ $item->accessLabel() }}</td>
                            <td>
                                @if ($item->price)
                                    {{ number_format($item->price, 0).' €' }}
                                    @if ($item->price_gnf)
                                        <br><small>{{ number_format($item->price_gnf, 0, ',', ' ') }} GNF</small>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if ($item->isExclusive() && $item->isSoldExclusively())
                                    <span class="badge badge-sold">Vendu</span>
                                @elseif ($item->isExclusive())
                                    <span class="badge badge-exclusive">Exclusif</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <span @class(['badge', 'badge-published' => $item->status === 'published', 'badge-draft' => $item->status === 'draft'])>
                                    {{ $item->status === 'published' ? 'Publié' : 'Brouillon' }}
                                </span>
                            </td>
                            <td class="actions">
                                <a href="{{ route('admin.contents.edit', $item) }}" class="btn btn-sm btn-outline btn-icon" title="Modifier"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                <form action="{{ route('admin.contents.destroy', $item) }}" method="POST" onsubmit="return confirm('Supprimer ce contenu ?')">
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

            <div class="pagination-wrap">{{ $contents->links() }}</div>
        @endif
    </div>
@endsection
