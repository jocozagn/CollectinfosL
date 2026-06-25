<?php

namespace App\Http\Controllers;

use App\Models\CollaborationRequest;
use App\Models\Content;
use App\Models\Investigation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CollaborationController extends Controller
{
    public function index(): View
    {
        $investigations = Investigation::open()
            ->latest('published_at')
            ->get();

        $user = Auth::user();

        return view('pages.collaboration', [
            'investigations' => $investigations,
            'themes' => Content::themeLabels(),
            'authUser' => $user,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'type' => ['required', 'in:join,propose'],
            'investigation_id' => ['nullable', 'exists:investigations,id'],
            'proposed_title' => ['nullable', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        if ($data['type'] === 'join' && empty($data['investigation_id'])) {
            return back()->withErrors(['investigation_id' => 'Veuillez sélectionner une enquête.'])->withInput();
        }

        if ($data['type'] === 'propose' && empty(trim($data['proposed_title'] ?? ''))) {
            return back()->withErrors(['proposed_title' => 'Indiquez le titre de l\'enquête proposée.'])->withInput();
        }

        CollaborationRequest::create([
            'user_id' => $user?->id,
            'investigation_id' => $data['type'] === 'join' ? $data['investigation_id'] : null,
            'type' => $data['type'],
            'proposed_title' => $data['type'] === 'propose' ? $data['proposed_title'] : null,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'country' => $data['country'] ?? null,
            'message' => $data['message'],
            'status' => 'pending',
        ]);

        $msg = $data['type'] === 'propose'
            ? 'Votre proposition d\'enquête a été reçue. Une fois acceptée, vous accéderez à l\'espace collaboratif (discussion, fichiers, validation des contenus).'
            : 'Votre candidature a été enregistrée. Le porteur de l\'enquête ou l\'équipe Collectinfos l\'examinera. Si elle est acceptée, vous pourrez collaborer via l\'espace sécurisé de l\'enquête.';

        $redirect = $user && $user->isJournalist()
            ? redirect()->route('account', ['tab' => 'applications'])->with('account_success', $msg)
            : redirect()->to(route('collaboration').'#collaboration-form')->with('collaboration_success', $msg);

        return $redirect;
    }
}
