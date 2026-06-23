@extends('admin.layouts.app')

@section('title', 'Newsletter')
@section('page-title', 'Abonnés newsletter')

@section('content')
    <div class="admin-panel">
        <div class="panel-header">
            <h2>{{ $subscribers->total() }} abonné(s)</h2>
            <div class="panel-actions">
                <a href="{{ route('admin.newsletter.export') }}" class="btn btn-secondary"><i class="fa-solid fa-download" aria-hidden="true"></i> Exporter CSV</a>
            </div>
        </div>

        @if ($subscribers->isEmpty())
            <p class="empty-state">Aucun abonné pour le moment.</p>
        @else
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Nom</th>
                        <th>Inscrit le</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($subscribers as $item)
                        <tr>
                            <td><a href="mailto:{{ $item->email }}">{{ $item->email }}</a></td>
                            <td>{{ $item->name ?? '—' }}</td>
                            <td>{{ $item->subscribed_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="actions">
                                <form action="{{ route('admin.newsletter.destroy', $item) }}" method="POST" onsubmit="return confirm('Retirer cet abonné ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"><i class="fa-solid fa-trash" aria-hidden="true"></i> Retirer</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="pagination-wrap">{{ $subscribers->links() }}</div>
        @endif
    </div>
@endsection
