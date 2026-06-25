<?php

namespace App\Services;

use App\Models\CollaborationRequest;
use App\Models\Investigation;
use Illuminate\Validation\ValidationException;

class CollaborationRequestService
{
    public function __construct(
        private CollaborationAcceptanceService $acceptanceService
    ) {}

    public function updateStatus(CollaborationRequest $request, string $newStatus): void
    {
        if (! in_array($newStatus, ['pending', 'accepted', 'rejected'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Statut invalide.',
            ]);
        }

        if ($newStatus === 'accepted' && $request->type === 'join') {
            $this->ensureJoinCanBeAccepted($request);
        }

        $previousStatus = $request->status;

        if ($previousStatus === $newStatus) {
            return;
        }

        $request->update(['status' => $newStatus]);

        $this->acceptanceService->handleStatusChange($request->fresh(), $previousStatus, $newStatus);
    }

    private function ensureJoinCanBeAccepted(CollaborationRequest $request): void
    {
        $investigation = $request->investigation;

        if (! $investigation) {
            throw ValidationException::withMessages([
                'status' => 'Aucune enquête associée à cette candidature.',
            ]);
        }

        if (! $investigation->hasAvailablePlace()) {
            throw ValidationException::withMessages([
                'status' => 'Toutes les places de cette enquête sont déjà occupées.',
            ]);
        }

        $candidateUser = $request->user_id
            ? $request->user
            : \App\Models\User::query()->where('email', $request->email)->first();

        if ($candidateUser && $investigation->isMember($candidateUser)) {
            throw ValidationException::withMessages([
                'status' => 'Ce journaliste participe déjà à l\'enquête.',
            ]);
        }
    }

    public function pendingJoinRequestsFor(Investigation $investigation)
    {
        return CollaborationRequest::query()
            ->with('user')
            ->where('investigation_id', $investigation->id)
            ->where('type', 'join')
            ->where('status', 'pending')
            ->latest()
            ->get();
    }
}
