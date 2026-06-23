@extends('admin.layouts.app')

@section('title', 'Messages contact')
@section('page-title', 'Messages contact')

@section('content')
    <div class="admin-panel">
        <div class="panel-header">
            <h2>{{ $messages->total() }} message(s)</h2>
        </div>

        @if ($messages->isEmpty())
            <p class="empty-state">Aucun message reçu pour le moment.</p>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Sujet</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($messages as $item)
                        <tr>
                            <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $item->name }}</td>
                            <td><a href="mailto:{{ $item->email }}">{{ $item->email }}</a></td>
                            <td>{{ Str::limit($item->subject, 50) }}</td>
                            <td class="actions">
                                <a href="{{ route('admin.messages.show', $item) }}" class="btn btn-sm"><i class="fa-solid fa-eye" aria-hidden="true"></i> Lire</a>
                                <form action="{{ route('admin.messages.destroy', $item) }}" method="POST" onsubmit="return confirm('Supprimer ce message ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash" aria-hidden="true"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pagination-wrap">{{ $messages->links() }}</div>
        @endif
    </div>
@endsection
