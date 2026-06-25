@extends('admin.layouts.app')



@section('title', 'Demande presse')

@section('page-title', 'Demande relations presse')



@section('content')

    <div class="admin-panel">

        <div class="panel-header">

            <h2>{{ $request->company_name }}</h2>

            <div class="panel-header-actions">

                <a href="{{ route('admin.press-requests.index') }}" class="btn btn-secondary btn-sm"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Retour</a>

                <a href="mailto:{{ $request->email }}?subject={{ rawurlencode('Collectinfos – Relation presse') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-reply" aria-hidden="true"></i> Répondre</a>

                <form action="{{ route('admin.press-requests.destroy', $request) }}" method="POST" onsubmit="return confirm('Supprimer cette demande ?')">

                    @csrf

                    @method('DELETE')

                    <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash" aria-hidden="true"></i> Supprimer</button>

                </form>

            </div>

        </div>



        <div class="detail-grid">

            <div class="detail-item">

                <label>Entreprise</label>

                <p>{{ $request->company_name }}</p>

            </div>

            <div class="detail-item">

                <label>E-mail</label>

                <p><a href="mailto:{{ $request->email }}">{{ $request->email }}</a></p>

            </div>

            <div class="detail-item">

                <label>Pays</label>

                <p>{{ $request->country }}</p>

            </div>

            <div class="detail-item">

                <label>Expérience</label>

                <p>{{ $request->experienceLabel() }}</p>

            </div>

            <div class="detail-item">

                <label>Reçu le</label>

                <p>{{ $request->created_at->format('d/m/Y à H:i') }}</p>

            </div>

            <div class="detail-item">

                <label>Thématiques</label>

                <p>{{ $request->topicsLabels() ? implode(', ', $request->topicsLabels()) : '—' }}</p>

            </div>

        </div>



        @if ($request->company_experience)

            <h3 style="font-size: 0.9375rem; margin: 0 0 8px;">Expérience de l'entreprise</h3>

            <div class="message-body" style="margin-bottom: 20px;">{{ $request->company_experience }}</div>

        @endif



        <h3 style="font-size: 0.9375rem; margin: 0 0 8px;">Que pouvons-nous faire pour vous ?</h3>

        <div class="message-body">{{ $request->message }}</div>

    </div>

@endsection

