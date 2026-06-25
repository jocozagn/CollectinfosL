<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentSubmission;
use App\Services\ContentSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContentSubmissionController extends Controller
{
    public function __construct(private ContentSubmissionService $submissions) {}

    public function index(Request $request): View
    {
        $query = ContentSubmission::query()->with('author')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('title', 'like', "%{$q}%")
                    ->orWhere('country', 'like', "%{$q}%");
            });
        }

        return view('admin.submissions.index', [
            'submissions' => $query->paginate(15)->withQueryString(),
            'statuses' => ContentSubmission::statusLabels(),
        ]);
    }

    public function show(ContentSubmission $submission): View
    {
        $submission->load(['author', 'content', 'reviewer']);

        return view('admin.submissions.show', [
            'submission' => $submission,
        ]);
    }

    public function update(Request $request, ContentSubmission $submission): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:review,approve,publish,reject'],
            'review_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $reviewer = auth()->user();

        if ($data['action'] === 'review') {
            $submission->update(['status' => ContentSubmission::STATUS_IN_REVIEW]);

            return back()->with('success', 'Soumission passée en modération.');
        }

        if ($data['action'] === 'reject') {
            $request->validate(['review_note' => ['required', 'string', 'max:2000']]);
            $this->submissions->reject($submission, $reviewer, $data['review_note']);

            return redirect()->route('admin.submissions.index')
                ->with('success', 'Soumission rejetée.');
        }

        if (in_array($data['action'], ['approve', 'publish'], true)) {
            $this->submissions->approve($submission, $reviewer, $data['action'] === 'publish', $data['review_note']);

            return redirect()->route('admin.submissions.index')
                ->with('success', 'Soumission validée et contenu créé.');
        }

        return back();
    }
}
