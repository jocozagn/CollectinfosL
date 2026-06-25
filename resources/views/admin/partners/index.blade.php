@extends('admin.layouts.app')



@section('title', 'Partenaires médias')

@section('page-title', 'Partenaires médias')



@section('content')

    <div class="admin-panel admin-panel--data">

        <div class="panel-header">

            <h2>{{ $partners->total() }} partenaire(s)</h2>

            <div class="panel-header-actions">

                <a href="{{ route('admin.partners.create') }}" class="btn btn-primary btn-sm">

                    <i class="fa-solid fa-plus" aria-hidden="true"></i> Ajouter

                </a>

            </div>

        </div>



        @if ($partners->isEmpty())

            <p class="empty-state">Aucun partenaire. <a href="{{ route('admin.partners.create') }}">Ajouter le premier</a>.</p>

        @else

            <div class="table-wrap">

            <table class="admin-table">

                <thead>

                    <tr>

                        <th>Logo</th>

                        <th>Nom</th>

                        <th>URL</th>

                        <th>Ordre</th>

                        <th>Statut</th>

                        <th>Actions</th>

                    </tr>

                </thead>

                <tbody>

                    @foreach ($partners as $item)

                        <tr>

                            <td>

                                @if ($item->logoUrl())

                                    <img src="{{ $item->logoUrl() }}" alt="" class="thumb-preview">

                                @endif

                            </td>

                            <td>{{ $item->name ?? '—' }}</td>

                            <td>

                                @if ($item->url)

                                    <a href="{{ $item->url }}" target="_blank" rel="noopener">Lien</a>

                                @else

                                    —

                                @endif

                            </td>

                            <td>{{ $item->sort_order }}</td>

                            <td>

                                <span @class(['badge', 'badge-published' => $item->is_active, 'badge-draft' => ! $item->is_active])>

                                    {{ $item->is_active ? 'Actif' : 'Inactif' }}

                                </span>

                            </td>

                            <td class="actions">

                                <a href="{{ route('admin.partners.edit', $item) }}" class="btn btn-sm btn-outline btn-icon" title="Modifier"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>

                                <form action="{{ route('admin.partners.destroy', $item) }}" method="POST" onsubmit="return confirm('Supprimer ?')">

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



            <div class="pagination-wrap">{{ $partners->links() }}</div>

        @endif

    </div>

@endsection

