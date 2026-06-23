@extends('admin.layouts.app')

@section('title', 'Message contact')
@section('page-title', 'Message contact')

@section('content')
    <div class="admin-panel">
        <div class="panel-header">
            <h2>{{ $message->subject }}</h2>
            <div class="panel-actions">
                <a href="{{ route('admin.messages.index') }}" class="btn btn-secondary"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour</a>
                <a href="mailto:{{ $message->email }}?subject=Re: {{ rawurlencode($message->subject) }}" class="btn btn-primary"><i class="fa-solid fa-reply" aria-hidden="true"></i> Répondre</a>
                <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" onsubmit="return confirm('Supprimer ce message ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash" aria-hidden="true"></i> Supprimer</button>
                </form>
            </div>
        </div>

        <div class="detail-grid">
            <div class="detail-item">
                <label>De</label>
                <p>{{ $message->name }}</p>
            </div>
            <div class="detail-item">
                <label>Email</label>
                <p><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></p>
            </div>
            <div class="detail-item">
                <label>Reçu le</label>
                <p>{{ $message->created_at->format('d/m/Y à H:i') }}</p>
            </div>
        </div>

        <div class="message-body">{{ $message->message }}</div>
    </div>
@endsection
