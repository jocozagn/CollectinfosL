@extends('admin.layouts.app')

@section('title', 'Commande #'.$order->id)
@section('page-title', $order->title)

@section('content')
    <div class="admin-panel">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="detail-grid">
            <div class="detail-item"><label>Acheteur</label><p>{{ $order->buyer?->name }} — {{ $order->buyer?->email }}</p></div>
            <div class="detail-item"><label>Statut</label><p>{{ $order->statusLabel() }}</p></div>
            <div class="detail-item detail-item--full"><label>Description</label><p>{{ $order->description }}</p></div>
            <div class="detail-item"><label>Pays</label><p>{{ $order->country ?? '—' }}</p></div>
            <div class="detail-item"><label>Budget</label><p>{{ $order->budget ? number_format($order->budget, 0).' €' : '—' }}</p></div>
            <div class="detail-item"><label>Échéance</label><p>{{ $order->deadline?->format('d/m/Y') ?? '—' }}</p></div>
        </div>

        <form class="admin-form" method="POST" action="{{ route('admin.orders.update', $order) }}">
            @csrf
            @method('PUT')
            <div class="form-row">
                <div class="form-group">
                    <label for="status">Statut</label>
                    <select id="status" name="status" required>
                        @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}" @selected(old('status', $order->status) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="assigned_journalist_id">Journaliste assigné</label>
                    <select id="assigned_journalist_id" name="assigned_journalist_id">
                        <option value="">—</option>
                        @foreach ($journalists as $journalist)
                            <option value="{{ $journalist->id }}" @selected(old('assigned_journalist_id', $order->assigned_journalist_id) == $journalist->id)>{{ $journalist->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="admin_note">Note interne</label>
                <textarea id="admin_note" name="admin_note" rows="2">{{ old('admin_note', $order->admin_note) }}</textarea>
            </div>
            <div class="form-group">
                <label for="delivery_note">Note de livraison (visible acheteur)</label>
                <textarea id="delivery_note" name="delivery_note" rows="2">{{ old('delivery_note', $order->delivery_note) }}</textarea>
            </div>
            <div class="form-actions">
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Retour</a>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
@endsection
