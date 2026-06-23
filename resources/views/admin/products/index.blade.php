@extends('admin.layouts.app')

@section('title', 'Nos produits')
@section('page-title', 'Offres & produits')

@section('content')
    <div class="admin-panel admin-panel--data">
        <div class="panel-header">
            <h2>{{ $products->total() }} offre(s)</h2>
            <div class="panel-header-actions">
                <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i> Ajouter
                </a>
            </div>
        </div>

        @if ($products->isEmpty())
            <p class="empty-state">Aucune offre. <a href="{{ route('admin.products.create') }}">Créer la première</a>.</p>
        @else
            <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Icône</th>
                        <th>Nom</th>
                        <th>Prix</th>
                        <th>Ordre</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($products as $item)
                        <tr>
                            <td><i class="fa-solid {{ $item->icon }}" aria-hidden="true"></i></td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->price }}</td>
                            <td>{{ $item->sort_order }}</td>
                            <td>
                                <span @class(['badge', 'badge-published' => $item->is_active, 'badge-draft' => ! $item->is_active])>
                                    {{ $item->is_active ? 'Actif' : 'Inactif' }}
                                </span>
                            </td>
                            <td class="actions">
                                <a href="{{ route('admin.products.edit', $item) }}" class="btn btn-sm btn-outline btn-icon" title="Modifier"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
                                <form action="{{ route('admin.products.destroy', $item) }}" method="POST" onsubmit="return confirm('Supprimer ?')">
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

            <div class="pagination-wrap">{{ $products->links() }}</div>
        @endif
    </div>
@endsection
