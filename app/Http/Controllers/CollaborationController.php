<?php

namespace App\Http\Controllers;

use App\Models\CollaborationRequest;
use App\Models\Content;
use App\Models\Investigation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CollaborationController extends Controller
{
    public function index(): View
    {
        $investigations = Investigation::open()
            ->latest('published_at')
            ->get();

        return view('pages.collaboration', [
            'investigations' => $investigations,
            'themes' => Content::themeLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:join,propose'],
            'investigation_id' => ['nullable', 'exists:investigations,id'],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        if ($data['type'] === 'join' && empty($data['investigation_id'])) {
            return back()->withErrors(['investigation_id' => 'Veuillez sélectionner une enquête.'])->withInput();
        }

        CollaborationRequest::create($data);

        $msg = $data['type'] === 'propose'
            ? 'Votre proposition d\'enquête a été reçue. Notre équipe vous contactera prochainement.'
            : 'Votre candidature a été enregistrée. Nous reviendrons vers vous rapidement.';

        return redirect()->to(route('collaboration').'#collaboration-form')
            ->with('collaboration_success', $msg);
    }
}
