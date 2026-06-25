<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\ContentSubmission;
use App\Models\Taxonomy;
use App\Services\ContentSubmissionService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContentSubmissionController extends Controller
{
    public function __construct(
        private ContentSubmissionService $submissions,
        private NotificationService $notifications,
    ) {}

    public function create(): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user || ! $user->isJournalist()) {
            return redirect()->route('account')
                ->with('error', 'Seuls les journalistes peuvent déposer un contenu.');
        }

        return view('pages.submit-content', [
            'types' => Content::typeLabels(),
            'themes' => Content::themeLabels(),
            'categories' => Content::categoryLabels(),
            'accessOptions' => Content::accessLabels(),
            'rightsOptions' => config('collectinfos.rights_options', []),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if (! $user || ! $user->isJournalist()) {
            abort(403);
        }

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string', 'max:2500'],
            'type' => ['required', Rule::exists('taxonomies', 'slug')->where('kind', Taxonomy::KIND_TYPE)->where('is_active', true)],
            'theme' => ['nullable', Rule::exists('taxonomies', 'slug')->where('kind', Taxonomy::KIND_THEME)->where('is_active', true)],
            'category' => ['nullable', Rule::exists('taxonomies', 'slug')->where('kind', Taxonomy::KIND_CATEGORY)->where('is_active', true)],
            'country' => ['nullable', 'string', 'max:100'],
            'region' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'keywords' => ['nullable', 'string', 'max:255'],
            'access' => ['required', 'in:free,subscriber,exclusive'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'rights' => ['nullable', 'string', 'max:50'],
            'negotiable' => ['nullable', 'boolean'],
            'resolution' => ['nullable', 'string', 'max:50'],
            'duration' => ['nullable', 'string', 'max:50'],
            'content_date' => ['nullable', 'date'],
            'exclusivity_expires_at' => ['nullable', 'date'],
            'gps_lat' => ['nullable', 'string', 'max:30'],
            'gps_lng' => ['nullable', 'string', 'max:30'],
            'thumbnail' => ['nullable', 'image', 'max:5120'],
            'media_file' => ['nullable', 'file', 'max:512000'],
        ]);

        if ($data['access'] === 'exclusive' && empty($data['price'])) {
            return back()->withErrors(['price' => 'Un prix est obligatoire pour une exclusivité.'])->withInput();
        }

        $metadataErrors = $this->submissions->validateMetadata($data);
        if ($metadataErrors !== []) {
            return back()->withErrors(['summary' => implode(' ', $metadataErrors)])->withInput();
        }

        $data = $this->submissions->storeUploads($request, $data);
        $data['user_id'] = $user->id;
        $data['status'] = ContentSubmission::STATUS_PENDING;
        $data['negotiable'] = $request->boolean('negotiable');

        $submission = ContentSubmission::create($data);

        $this->notifications->notify(
            $user,
            'submission_received',
            'Soumission reçue',
            'Votre contenu « '.$submission->title.' » est en attente de modération.',
            route('account', ['tab' => 'publications'])
        );

        return redirect()->route('account', ['tab' => 'publications'])
            ->with('account_success', 'Votre contenu a été soumis. Il sera publié après validation par notre équipe.');
    }
}
