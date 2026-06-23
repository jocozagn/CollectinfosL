<?php

namespace App\Http\Controllers;

use App\Models\PressRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PressController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $topicKeys = array_keys(config('collectinfos.press.topics', []));

        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:10'],
            'company_experience' => ['nullable', 'string', 'max:2000'],
            'topics' => ['required', 'array', 'min:1'],
            'topics.*' => ['string', Rule::in($topicKeys)],
            'topics_other' => ['nullable', 'string', 'max:150'],
            'country' => ['required', 'string', 'max:100'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        if (in_array('other', $data['topics'], true) && empty(trim($data['topics_other'] ?? ''))) {
            return back()
                ->withInput()
                ->withErrors(['topics_other' => 'Précisez votre thématique « Autre ».']);
        }

        PressRequest::create([
            'company_name' => $data['company_name'],
            'email' => $data['email'],
            'experience_years' => $data['experience_years'],
            'company_experience' => $data['company_experience'] ?? null,
            'topics' => $data['topics'],
            'topics_other' => $data['topics_other'] ?? null,
            'country' => $data['country'],
            'message' => $data['message'],
        ]);

        return redirect()->to(route('press').'#press-form')
            ->with('press_success', 'Votre demande presse a bien été envoyée. Nous vous recontacterons rapidement.');
    }
}
