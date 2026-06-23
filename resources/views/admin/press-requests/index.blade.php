@extends('admin.layouts.app')

@section('title', 'Demandes presse')
@section('page-title', 'Demandes relations presse')

@section('content')
    <div class="admin-panel admin-panel--data">
        <div class="panel-header">
            <h2>{{ $requests->total() }} demande(s)</h2>
        </div>

        @if ($requests->isEmpty())
            <p class="empty-state">Aucune demande presse pour le moment.</p>
        @else
            <div class="table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Entreprise</th>
                        <th>E-mail</th>
                        <th>Pays</th>
                        <th>Expérience</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($requests as $item)
                        <tr>
                            <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $item->company_name }}</td>
                            <td><a href="mailto:{{ $item->email }}">{{ $item->email }}</a></td>
                            <td>{{ $item->country }}</td>
                            <td>{{ $item->experienceLabel() }}</td>
                            <td class="actions">
                                <a href="{{ route('admin.press-requests.show', $item) }}" class="btn btn-sm btn-outline"><i class="fa-solid fa-eye" aria-hidden="true"></i> Voir</a>
                                <form action="{{ route('admin.press-requests.destroy', $item) }}" method="POST" onsubmit="return confirm('Supprimer cette demande ?')">
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

            <div class="pagination-wrap">{{ $requests->links() }}</div>
        @endif
    </div>
@endsection
