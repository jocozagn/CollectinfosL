@extends('admin.layouts.app')



@section('title', 'Statistiques du site')

@section('page-title', 'Statistiques du site')



@section('content')

    <div class="admin-panel admin-panel--data">

        <div class="panel-header">

            <h2>Chiffres affichés sur l'accueil</h2>

            <div class="panel-header-actions">

                <a href="{{ route('admin.site-stats.create') }}" class="btn btn-primary btn-sm">

                    <i class="fa-solid fa-plus" aria-hidden="true"></i> Ajouter

                </a>

            </div>

        </div>



        @if ($stats->isEmpty())

            <p class="empty-state">Aucune statistique. <a href="{{ route('admin.site-stats.create') }}">Ajouter la première</a>.</p>

        @else

            <div class="table-wrap">

            <table class="admin-table">

                <thead>

                    <tr>

                        <th>Valeur</th>

                        <th>Libellé</th>

                        <th>Ordre</th>

                        <th>Statut</th>

                        <th>Actions</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach ($stats as $item)

                        <tr>

                            <td><strong>{{ number_format($item->value, 0, ',', ' ') }}</strong></td>

                            <td>{{ $item->label }}</td>

                            <td>{{ $item->sort_order }}</td>

                            <td>

                                <span @class(['badge', 'badge-published' => $item->is_active, 'badge-draft' => ! $item->is_active])>

                                    {{ $item->is_active ? 'Actif' : 'Inactif' }}

                                </span>

                            </td>

                            <td class="actions">

                                <a href="{{ route('admin.site-stats.edit', $item) }}" class="btn btn-sm btn-outline btn-icon" title="Modifier"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>

                                <form action="{{ route('admin.site-stats.destroy', $item) }}" method="POST" onsubmit="return confirm('Supprimer ?')">

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



            <div class="pagination-wrap">{{ $stats->links() }}</div>

        @endif

    </div>

@endsection

