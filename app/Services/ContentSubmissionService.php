<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ContentPurchase;
use App\Models\ContentSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContentSubmissionService
{
    public function __construct(
        private NotificationService $notifications,
    ) {}

    public function approve(ContentSubmission $submission, User $reviewer, bool $publish = true, ?string $note = null): Content
    {
        return DB::transaction(function () use ($submission, $reviewer, $publish, $note) {
            $submission = ContentSubmission::query()->lockForUpdate()->find($submission->id);

            $content = Content::create([
                'user_id' => $submission->user_id,
                'title' => $submission->title,
                'slug' => Content::generateSlug($submission->title),
                'summary' => $submission->summary,
                'type' => $submission->type,
                'theme' => $submission->theme,
                'country' => $submission->country,
                'category' => $submission->category,
                'access' => $submission->access,
                'price' => $submission->price,
                'duration' => $submission->duration,
                'thumbnail' => $submission->thumbnail,
                'media_path' => $submission->media_path,
                'status' => $publish ? 'published' : 'draft',
                'published_at' => $publish ? now() : null,
            ]);

            $submission->update([
                'content_id' => $content->id,
                'status' => $publish ? ContentSubmission::STATUS_PUBLISHED : ContentSubmission::STATUS_APPROVED,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'review_note' => $note,
            ]);

            $this->notifications->notify(
                $submission->author,
                'submission_approved',
                'Contenu validé',
                'Votre soumission « '.$submission->title.' » a été '.($publish ? 'publiée' : 'validée').'.',
                route('contents.show', $content->slug)
            );

            return $content;
        });
    }

    public function reject(ContentSubmission $submission, User $reviewer, string $note): void
    {
        $submission->update([
            'status' => ContentSubmission::STATUS_REJECTED,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_note' => $note,
        ]);

        $this->notifications->notify(
            $submission->author,
            'submission_rejected',
            'Contenu rejeté',
            'Votre soumission « '.$submission->title.' » a été refusée. Motif : '.$note,
            route('account', ['tab' => 'publications'])
        );
    }

    public function validateMetadata(array $data): array
    {
        $errors = [];

        if (mb_strlen($data['summary'] ?? '') > 2500) {
            $errors[] = 'Le pitch dépasse 2500 caractères.';
        }

        if (($data['access'] ?? 'free') === 'exclusive' && empty($data['price'])) {
            $errors[] = 'Un prix est requis pour une exclusivité.';
        }

        return $errors;
    }

    public function storeUploads($request, array $data): array
    {
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('submissions/thumbnails', 'public');
        }

        if ($request->hasFile('media_file')) {
            $file = $request->file('media_file');
            $data['media_path'] = $file->store('submissions/media', 'public');
            $data['file_format'] = strtoupper($file->getClientOriginalExtension());
            $data['file_size'] = $this->formatBytes($file->getSize());
        }

        return $data;
    }

    public function generateProfileSlug(User $user): string
    {
        $base = Str::slug($user->name) ?: 'journaliste';
        $slug = $base;
        $count = 1;

        while (User::where('profile_slug', $slug)->where('id', '!=', $user->id)->exists()) {
            $slug = $base.'-'.$count++;
        }

        return $slug;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1).' Mo';
        }

        if ($bytes >= 1024) {
            return round($bytes / 1024, 1).' Ko';
        }

        return $bytes.' o';
    }
}
