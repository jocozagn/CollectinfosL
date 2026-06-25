@extends('admin.layouts.app')

@section('title', 'Reversements')
@section('page-title', 'Reversements journalistes')

@section('content')
    <div class="admin-panel">
        <form class="admin-filters" method="GET">
            <select name="status">
                <option value="">Tous les statuts</option>
                @foreach ($statuses as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Filtrer</button>
        </form>

        <table class="admin-table">
            <thead>
                <tr><th>Journaliste</th><th>Montant</th><th>Mode</th><th>Statut</th><th>Date</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($requests as $item)
                    <tr>
                        <td>{{ $item->user?->name }}</td>
                        <td>{{ number_format($item->amount, 0) }} €</td>
                        <td>{{ $item->method }}</td>
                        <td>{{ $item->statusLabel() }}</td>
                        <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                        <td><a href="{{ route('admin.wallet-payouts.show', $item) }}" class="btn btn-secondary btn-sm">Traiter</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6">Aucune demande de reversement.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $requests->links() }}
    </div>
@endsection
