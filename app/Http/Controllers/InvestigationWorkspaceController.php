<?php

namespace App\Http\Controllers;

use App\Models\CollaborationRequest;
use App\Models\Investigation;
use App\Models\InvestigationDraft;
use App\Models\InvestigationFile;
use App\Models\InvestigationMessage;
use App\Models\InvestigationParticipant;
use App\Models\User;
use App\Services\InvestigationAccessService;
use App\Services\CollaborationRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvestigationWorkspaceController extends Controller
{
    public function __construct(
        private InvestigationAccessService $access,
        private CollaborationRequestService $candidatureService
    ) {}

    public function show(Request $request, Investigation $investigation): View
    {
        $user = Auth::user();
        $this->access->ensureMember($investigation, $user);

        $section = $request->query('section', 'discussion');
        if (! in_array($section, ['discussion', 'files', 'contents', 'team', 'candidatures'], true)) {
            $section = 'discussion';
        }

        $isOwner = $investigation->isOwner($user);

        if ($section === 'candidatures' && ! $isOwner) {
            abort(403);
        }

        $investigation->load([
            'owner',
            'participants.user',
            'messages' => fn ($query) => $query->with('user')->latest()->limit(100),
            'files' => fn ($query) => $query->with('user')->latest(),
            'drafts' => fn ($query) => $query->with(['author', 'reviewer'])->latest(),
        ]);

        $messages = $investigation->messages->sortBy('created_at')->values();
        $team = $this->buildTeamList($investigation);
        $pendingCandidatures = $isOwner
            ? $this->candidatureService->pendingJoinRequestsFor($investigation)
            : collect();

        return view('investigations.workspace', [
            'investigation' => $investigation,
            'user' => $user,
            'section' => $section,
            'messages' => $messages,
            'files' => $investigation->files,
            'drafts' => $investigation->drafts,
            'team' => $team,
            'pendingCandidatures' => $pendingCandidatures,
            'isOwner' => $isOwner,
            'canContribute' => $investigation->canContribute($user),
            'canManageTeam' => $investigation->canManageTeam($user),
            'canReviewDrafts' => $investigation->canReviewDrafts($user),
            'userRole' => $investigation->roleLabelFor($user),
        ]);
    }

    public function messages(Investigation $investigation): JsonResponse
    {
        $user = Auth::user();
        $this->access->ensureMember($investigation, $user);

        $afterId = (int) request()->query('after', 0);

        $query = $investigation->messages()
            ->with('user')
            ->orderBy('id');

        if ($afterId > 0) {
            $query->where('id', '>', $afterId);
        }

        $messages = $query->get()->map(fn (InvestigationMessage $message) => $this->formatMessage($message));

        return response()->json(['messages' => $messages]);
    }

    public function streamMessages(Request $request, Investigation $investigation): StreamedResponse
    {
        $user = Auth::user();
        $this->access->ensureMember($investigation, $user);

        $afterId = (int) $request->query('after', 0);

        return response()->stream(function () use ($investigation, $afterId) {
            if (session()->isStarted()) {
                session()->save();
            }

            if (function_exists('apache_setenv')) {
                @apache_setenv('no-gzip', '1');
            }

            @ini_set('zlib.output_compression', '0');
            @ini_set('implicit_flush', '1');

            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            $lastId = $afterId;
            $deadline = time() + 50;

            while (time() < $deadline) {
                if (connection_aborted()) {
                    break;
                }

                $messages = $investigation->messages()
                    ->with('user')
                    ->where('id', '>', $lastId)
                    ->orderBy('id')
                    ->get();

                foreach ($messages as $message) {
                    $lastId = $message->id;
                    echo 'data: '.json_encode([
                        'type' => 'message',
                        'message' => $this->formatMessage($message),
                    ], JSON_UNESCAPED_UNICODE)."\n\n";
                    flush();
                }

                echo ": ping\n\n";
                flush();
                usleep(500000);
            }

            echo 'data: '.json_encode(['type' => 'reconnect'])."\n\n";
            flush();
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function storeMessage(Request $request, Investigation $investigation): JsonResponse|RedirectResponse
    {
        $user = Auth::user();
        $this->access->ensureContributor($investigation, $user);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message = $investigation->messages()->create([
            'user_id' => $user->id,
            'body' => trim($data['body']),
        ]);

        $message->load('user');

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'message' => $this->formatMessage($message),
            ], 201);
        }

        return redirect()
            ->route('account.investigations.show', ['investigation' => $investigation->slug, 'section' => 'discussion'])
            ->with('workspace_success', 'Message envoyé.');
    }

    public function storeFile(Request $request, Investigation $investigation): RedirectResponse
    {
        $user = Auth::user();
        $this->access->ensureContributor($investigation, $user);

        $data = $request->validate([
            'file' => ['required', 'file', 'max:51200'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $uploaded = $request->file('file');
        $path = $uploaded->store('investigations/'.$investigation->id, 'public');

        $investigation->files()->create([
            'user_id' => $user->id,
            'original_name' => $uploaded->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $uploaded->getClientMimeType(),
            'size' => $uploaded->getSize(),
            'description' => $data['description'] ?? null,
        ]);

        return redirect()
            ->route('account.investigations.show', ['investigation' => $investigation->slug, 'section' => 'files'])
            ->with('workspace_success', 'Fichier partagé avec l\'équipe.');
    }

    public function downloadFile(Investigation $investigation, InvestigationFile $file): StreamedResponse
    {
        $user = Auth::user();
        $this->access->ensureMember($investigation, $user);

        if ($file->investigation_id !== $investigation->id) {
            abort(404);
        }

        if (! Storage::disk('public')->exists($file->path)) {
            abort(404);
        }

        return Storage::disk('public')->download($file->path, $file->original_name);
    }

    public function destroyFile(Investigation $investigation, InvestigationFile $file): RedirectResponse
    {
        $user = Auth::user();
        $this->access->ensureMember($investigation, $user);

        if ($file->investigation_id !== $investigation->id) {
            abort(404);
        }

        if ($file->user_id !== $user->id && ! $investigation->canManageTeam($user)) {
            abort(403);
        }

        $file->deleteFromStorage();
        $file->delete();

        return redirect()
            ->route('account.investigations.show', ['investigation' => $investigation->slug, 'section' => 'files'])
            ->with('workspace_success', 'Fichier supprimé.');
    }

    public function storeDraft(Request $request, Investigation $investigation): RedirectResponse
    {
        $user = Auth::user();
        $this->access->ensureContributor($investigation, $user);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:50000'],
            'submit' => ['nullable', 'boolean'],
            'draft_id' => ['nullable', 'integer', 'exists:investigation_drafts,id'],
        ]);

        $submit = $request->boolean('submit');
        $status = $submit ? InvestigationDraft::STATUS_PENDING_REVIEW : InvestigationDraft::STATUS_DRAFT;

        if (! empty($data['draft_id'])) {
            $draft = InvestigationDraft::query()
                ->where('investigation_id', $investigation->id)
                ->findOrFail($data['draft_id']);

            if (! $draft->isEditableBy($user)) {
                abort(403);
            }

            $draft->update([
                'title' => $data['title'],
                'body' => $data['body'],
                'status' => $status,
                'submitted_at' => $submit ? now() : null,
                'reviewed_by' => null,
                'review_note' => null,
                'reviewed_at' => null,
            ]);
        } else {
            $investigation->drafts()->create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'body' => $data['body'],
                'status' => $status,
                'submitted_at' => $submit ? now() : null,
            ]);
        }

        $message = $submit
            ? 'Contenu soumis pour validation par le porteur ou un coordinateur.'
            : 'Brouillon enregistré.';

        return redirect()
            ->route('account.investigations.show', ['investigation' => $investigation->slug, 'section' => 'contents'])
            ->with('workspace_success', $message);
    }

    public function updateDraftStatus(Request $request, Investigation $investigation, InvestigationDraft $draft): RedirectResponse
    {
        $user = Auth::user();
        $this->access->ensureDraftReviewer($investigation, $user);

        if ($draft->investigation_id !== $investigation->id) {
            abort(404);
        }

        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'review_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $draft->update([
            'status' => $data['status'] === 'approved'
                ? InvestigationDraft::STATUS_APPROVED
                : InvestigationDraft::STATUS_REJECTED,
            'reviewed_by' => $user->id,
            'review_note' => $data['review_note'] ?? null,
            'reviewed_at' => now(),
        ]);

        $message = $data['status'] === 'approved'
            ? 'Contenu validé et co-signé.'
            : 'Contenu renvoyé pour révision.';

        return redirect()
            ->route('account.investigations.show', ['investigation' => $investigation->slug, 'section' => 'contents'])
            ->with('workspace_success', $message);
    }

    public function updateParticipantRole(Request $request, Investigation $investigation, User $member): RedirectResponse
    {
        $user = Auth::user();
        $this->access->ensureTeamManager($investigation, $user);

        if ($investigation->isOwner($member)) {
            return back()->withErrors(['role' => 'Le rôle du porteur ne peut pas être modifié ici.']);
        }

        $data = $request->validate([
            'role' => ['required', 'in:lead,contributor,viewer'],
        ]);

        $participant = InvestigationParticipant::query()
            ->where('investigation_id', $investigation->id)
            ->where('user_id', $member->id)
            ->firstOrFail();

        $participant->update(['role' => $data['role']]);

        return redirect()
            ->route('account.investigations.show', ['investigation' => $investigation->slug, 'section' => 'team'])
            ->with('workspace_success', 'Rôle mis à jour.');
    }

    public function updateCandidature(Request $request, Investigation $investigation, CollaborationRequest $collaboration): RedirectResponse
    {
        $user = Auth::user();
        $this->access->ensureOwner($investigation, $user);

        if ($collaboration->investigation_id !== $investigation->id) {
            abort(404);
        }

        if ($collaboration->type !== 'join') {
            abort(403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:accepted,rejected'],
        ]);

        try {
            $this->candidatureService->updateStatus($collaboration, $data['status']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        $message = $data['status'] === 'accepted'
            ? 'Candidature acceptée. Le journaliste a rejoint l\'enquête.'
            : 'Candidature refusée.';

        return redirect()
            ->route('account.investigations.show', ['investigation' => $investigation->slug, 'section' => 'candidatures'])
            ->with('workspace_success', $message);
    }

    private function buildTeamList(Investigation $investigation): array
    {
        $team = [];

        if ($investigation->owner) {
            $team[] = [
                'user' => $investigation->owner,
                'role' => 'owner',
                'role_label' => 'Porteur',
                'joined_at' => $investigation->created_at,
                'participant_id' => null,
            ];
        }

        foreach ($investigation->participants as $participant) {
            if ($investigation->owner && $participant->user_id === $investigation->owner->id) {
                continue;
            }

            $team[] = [
                'user' => $participant->user,
                'role' => $participant->role,
                'role_label' => $participant->roleLabel(),
                'joined_at' => $participant->joined_at,
                'participant_id' => $participant->id,
            ];
        }

        return $team;
    }

    private function formatMessage(InvestigationMessage $message): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'created_at' => $message->created_at->format('d/m/Y H:i'),
            'is_mine' => $message->user_id === Auth::id(),
            'user' => [
                'id' => $message->user->id,
                'name' => $message->user->name,
            ],
        ];
    }
}
