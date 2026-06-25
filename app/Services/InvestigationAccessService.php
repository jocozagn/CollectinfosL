<?php

namespace App\Services;

use App\Models\Investigation;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class InvestigationAccessService
{
    public function ensureMember(Investigation $investigation, User $user): void
    {
        if (! $investigation->isMember($user)) {
            throw new AccessDeniedHttpException('Vous n\'avez pas accès à cette enquête.');
        }
    }

    public function ensureContributor(Investigation $investigation, User $user): void
    {
        $this->ensureMember($investigation, $user);

        if (! $investigation->canContribute($user)) {
            throw new AccessDeniedHttpException('Votre rôle ne permet pas cette action.');
        }
    }

    public function ensureTeamManager(Investigation $investigation, User $user): void
    {
        $this->ensureMember($investigation, $user);

        if (! $investigation->canManageTeam($user)) {
            throw new AccessDeniedHttpException('Seul le porteur ou un coordinateur peut gérer l\'équipe.');
        }
    }

    public function ensureDraftReviewer(Investigation $investigation, User $user): void
    {
        $this->ensureMember($investigation, $user);

        if (! $investigation->canReviewDrafts($user)) {
            throw new AccessDeniedHttpException('Vous ne pouvez pas valider ce contenu.');
        }
    }

    public function ensureOwner(Investigation $investigation, User $user): void
    {
        if (! $investigation->isOwner($user)) {
            throw new AccessDeniedHttpException('Seul le porteur de l\'enquête peut effectuer cette action.');
        }
    }
}
