<?php

namespace App\Services;

use App\Models\CollaborationRequest;
use App\Models\Investigation;
use App\Models\InvestigationParticipant;
use App\Models\User;

class CollaborationAcceptanceService
{
    public function handleStatusChange(CollaborationRequest $request, string $previousStatus, string $newStatus): void
    {
        if ($newStatus !== 'accepted' || $previousStatus === 'accepted') {
            return;
        }

        if ($request->type === 'join') {
            $this->acceptJoin($request);

            return;
        }

        if ($request->type === 'propose') {
            $this->acceptPropose($request);
        }
    }

    private function acceptJoin(CollaborationRequest $request): void
    {
        if (! $request->investigation_id) {
            return;
        }

        $user = $this->resolveUser($request);

        if (! $user) {
            return;
        }

        InvestigationParticipant::query()->firstOrCreate(
            [
                'investigation_id' => $request->investigation_id,
                'user_id' => $user->id,
            ],
            [
                'collaboration_request_id' => $request->id,
                'role' => InvestigationParticipant::ROLE_CONTRIBUTOR,
                'joined_at' => now(),
            ]
        );
    }

    private function acceptPropose(CollaborationRequest $request): void
    {
        if ($request->investigation_id) {
            $investigation = $request->investigation;

            if ($investigation && ! $investigation->user_id) {
                $user = $this->resolveUser($request);
                if ($user) {
                    $investigation->update(['user_id' => $user->id]);
                }
            }

            return;
        }

        $user = $this->resolveUser($request);
        $title = $request->proposed_title ?: 'Enquête proposée par '.$request->name;

        $investigation = Investigation::create([
            'user_id' => $user?->id,
            'title' => $title,
            'slug' => Investigation::generateSlug($title),
            'summary' => $request->message,
            'country' => $request->country,
            'theme' => null,
            'places' => 3,
            'status' => 'pending',
            'published_at' => null,
        ]);

        $request->update(['investigation_id' => $investigation->id]);

        if ($user) {
            InvestigationParticipant::query()->firstOrCreate(
                [
                    'investigation_id' => $investigation->id,
                    'user_id' => $user->id,
                ],
                [
                    'collaboration_request_id' => $request->id,
                    'role' => InvestigationParticipant::ROLE_CONTRIBUTOR,
                    'joined_at' => now(),
                ]
            );
        }
    }

    private function resolveUser(CollaborationRequest $request): ?User
    {
        if ($request->user_id) {
            return User::find($request->user_id);
        }

        return User::query()->where('email', $request->email)->first();
    }
}
