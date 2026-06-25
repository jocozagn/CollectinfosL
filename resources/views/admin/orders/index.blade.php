@extends('admin.layouts.app')

@section('title', 'Commandes de contenus')
@section('page-title', 'Commandes de contenus')

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
                <tr><th>Sujet</th><th>Acheteur</th><th>Statut</th><th>Budget</th><th>Date</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td>{{ $order->title }}</td>
                        <td>{{ $order->buyer?->name }}</td>
                        <td>{{ $order->statusLabel() }}</td>
                        <td>{{ $order->budget ? number_format($order->budget, 0).' €' : '—' }}</td>
                        <td>{{ $order->created_at->format('d/m/Y') }}</td>
                        <td><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-secondary btn-sm">Gérer</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6">Aucune commande.</td></tr>
                @endforelse
            </tbody>
        </table>

        {{ $orders->links() }}
    </div>
@endsection
