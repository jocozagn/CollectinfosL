@extends('admin.layouts.app')

@section('title', $kindLabel)
@section('page-title', $kindLabel)

@section('content')
    <div class="admin-panel admin-panel--data">
        <div class="panel-header">
            <h2>{{ $items->total() }} élément(s)</h2>
            <div class="panel-header-actions">
                <a href="{{ route('admin.taxonomies.create', $kindRoute) }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i> Ajouter
                </a>
            </div>
        </div>

        @if ($items->isEmpty())
            <p class="empty-state">Aucun élément. <a href="{{ route('admin.taxonomies.create', $kindRoute) }}">Créer le premier</a>.</p>
        @else
            <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        @if ($kind === \App\Models\Taxonomy::KIND_CATEGORY)
                            <th>Image</th>
                        @endif
                        <th>Nom</th>
                        <th>Slug</th>
                        @if ($kind === \App\Models\Taxonomy::KIND_TYPE)
                            <th>Icône</th>
                        @endif
                        <th>Ordre</th>
                        <th>Statut</th>
                        @if ($kind === \App\Models\Taxonomy::KIND_CATEGORY)
                            <th>Accueil</th>
                        @endif
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            @if ($kind === \App\Models\Taxonomy::KIND_CATEGORY)
                                <td>
                                    @if ($item->imageUrl())
                                        <img src="{{ $item->imageUrl() }}" alt="" class="thumb-preview">
                                    @else
                                        <span class="thumb-placeholder">—</span>
                                    @endif
                                </td>
                            @endif
                            <td>{{ $item->name }}</td>
                            <td><code>{{ $item->slug }}</code></td>
                            @if ($kind === \App\Models\Taxonomy::KIND_TYPE)
                                <td>
                                    @if ($item->icon)
                                        <i class="fa-solid {{ $item->icon }}" aria-hidden="true"></i>
                                    @else
                                        —
                                    @endif
                                </td>
                            @endif
                            <td>{{ $item->sort_order }}</td>
                            <td>
                                <span @class(['badge', 'badge-published' => $item->is_active, 'badge-draft' => ! $item->is_active])>
                                    {{ $item->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            @if ($kind === \App\Models\Taxonomy::KIND_CATEGORY)
                                <td>{{ $item->show_on_home ? 'Oui' : 'Non' }}</td>
                            @endif
                            <td class="actions">
                                <a href="{{ route('admin.taxonomies.edit', [$kindRoute, $item]) }}" class="btn btn-sm btn-outline btn-icon" title="Modifier"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                <form action="{{ route('admin.taxonomies.destroy', [$kindRoute, $item]) }}" method="POST" onsubmit="return confirm('Supprimer cet élément ?')">
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

            <div class="pagination-wrap">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
