@extends('admin.layouts.app')

@section('title', 'Reversement #'.$payout->id)
@section('page-title', 'Demande de reversement')

@section('content')
    <div class="admin-panel">
        @if (session('success'))
            <div class="admin-alert admin-alert--success">{{ session('success') }}</div>
        @endif

        <div class="detail-grid">
            <div class="detail-item"><label>Journaliste</label><p>{{ $payout->user?->name }} ({{ $payout->user?->email }})</p></div>
            <div class="detail-item"><label>Montant</label><p><strong>{{ number_format($payout->amount, 0) }} €</strong></p></div>
            <div class="detail-item"><label>Mode</label><p>{{ $paymentMethods[$payout->method]['label'] ?? $payout->method }}</p></div>
            <div class="detail-item"><label>Statut</label><p>{{ $payout->statusLabel() }}</p></div>
            <div class="detail-item"><label>Demandé le</label><p>{{ $payout->created_at->format('d/m/Y H:i') }}</p></div>
            @if ($payout->payout_phone)
                <div class="detail-item"><label>Téléphone</label><p>{{ $payout->payout_phone }}</p></div>
            @endif
            @if ($payout->payout_details)
                <div class="detail-item detail-item--full"><label>Coordonnées</label><p>{{ $payout->payout_details }}</p></div>
            @endif
            @if ($payout->admin_note)
                <div class="detail-item detail-item--full"><label>Note admin</label><p>{{ $payout->admin_note }}</p></div>
            @endif
        </div>

        @if ($payout->isPending())
            <form class="admin-form" method="POST" action="{{ route('admin.wallet-payouts.update', $payout) }}">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label for="admin_note">Note (obligatoire en cas de refus)</label>
                    <textarea id="admin_note" name="admin_note" rows="3">{{ old('admin_note') }}</textarea>
                    @error('admin_note')<span class="form-error">{{ $message }}</span>@enderror
                </div>
                <div class="form-actions">
                    <button type="submit" name="action" value="pay" class="btn btn-primary">Marquer comme payé</button>
                    <button type="submit" name="action" value="reject" class="btn btn-secondary">Refuser</button>
                    <a href="{{ route('admin.wallet-payouts.index') }}" class="btn btn-secondary">Retour</a>
                </div>
            </form>
        @else
            <a href="{{ route('admin.wallet-payouts.index') }}" class="btn btn-secondary">Retour</a>
        @endif
    </div>
@endsection
