<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ContentSubmissionService;
use App\Services\ProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct(
        private ContentSubmissionService $submissions,
        private ProfileService $profiles,
    ) {}

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate($this->rules($user));

        $meta = $this->profiles->extractMetaFromRequest($data, $user);

        $user->fill([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'country' => $data['country'] ?? null,
            'city' => $data['city'] ?? null,
            'bio' => $data['bio'] ?? null,
            'account_type' => $data['account_type'] ?? $user->account_type,
            'profile_meta' => $meta,
        ]);

        if ($user->isJournalist() && $request->boolean('public_profile') && ! $user->profile_slug) {
            $user->profile_slug = $this->submissions->generateProfileSlug($user);
        }

        if ($user->isJournalist() && ! $request->boolean('public_profile')) {
            $user->profile_slug = null;
        }

        $user->save();

        return redirect()->route('account', ['tab' => 'profile'])
            ->with('account_success', 'Profil mis à jour.');
    }

    /** @return array<string, mixed> */
    private function rules(User $user): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'account_type' => ['nullable', Rule::in(array_keys(config('collectinfos.account_types', [])))],
            'public_profile' => ['nullable', 'boolean'],
            'payment_preference' => ['nullable', 'string', 'max:50'],
            'mobile_money_number' => ['nullable', 'string', 'max:50'],
            'bank_details' => ['nullable', 'string', 'max:1000'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'structure_type' => ['nullable', Rule::in(array_keys(config('collectinfos.buyer_structure_types', [])))],
            'organization_address' => ['nullable', 'string', 'max:500'],
            'website' => ['nullable', 'url', 'max:500'],
            'siret' => ['nullable', 'string', 'max:100'],
            'contact_name' => ['nullable', 'string', 'max:100'],
            'contact_title' => ['nullable', 'string', 'max:100'],
            'editorial_themes' => ['nullable', 'string', 'max:500'],
            'geo_priorities' => ['nullable', 'string', 'max:500'],
            'content_types_sought' => ['nullable', 'string', 'max:500'],
            'monthly_order_volume' => ['nullable', 'string', 'max:100'],
            'billing_address' => ['nullable', 'string', 'max:500'],
            'billing_details' => ['nullable', 'string', 'max:1000'],
        ];

        if ($user->isJournalist()) {
            $rules = array_merge($rules, [
                'specialties' => ['nullable', 'string', 'max:500'],
                'languages' => ['nullable', 'string', 'max:255'],
                'nationality' => ['nullable', 'string', 'max:100'],
                'coverage_zones' => ['nullable', 'string', 'max:500'],
                'experience_years' => ['nullable', 'integer', 'min:0', 'max:60'],
                'press_card' => ['nullable', 'string', 'max:100'],
                'portfolio_url' => ['nullable', 'url', 'max:500'],
                'media_worked' => ['nullable', 'string', 'max:500'],
                'linkedin_url' => ['nullable', 'url', 'max:500'],
                'twitter_url' => ['nullable', 'url', 'max:500'],
            ]);
        }

        return $rules;
    }
}
