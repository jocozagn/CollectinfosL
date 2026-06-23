<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function editContact(): View
    {
        return view('admin.settings.contact', [
            'contact' => SiteSetting::contact(),
        ]);
    }

    public function updateContact(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'zone' => ['nullable', 'string', 'max:500'],
        ]);

        SiteSetting::set('contact_phone', $data['phone']);
        SiteSetting::set('contact_email', $data['email']);
        SiteSetting::set('contact_zone', $data['zone'] ?? '');

        return redirect()->route('admin.settings.contact')
            ->with('success', 'Coordonnées mises à jour.');
    }

    public function editPress(): View
    {
        $defaults = config('collectinfos.press', []);

        return view('admin.settings.press', [
            'intro' => SiteSetting::get('press_intro', $defaults['intro'] ?? ''),
            'introEn' => SiteSetting::get('press_intro_en', ''),
            'introPt' => SiteSetting::get('press_intro_pt', ''),
            'servicesText' => SiteSetting::lines('press_services') ?: implode("\n", $defaults['services'] ?? []),
            'servicesTextEn' => SiteSetting::lines('press_services_en'),
            'servicesTextPt' => SiteSetting::lines('press_services_pt'),
        ]);
    }

    public function updatePress(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'intro' => ['required', 'string', 'max:1000'],
            'intro_en' => ['nullable', 'string', 'max:1000'],
            'intro_pt' => ['nullable', 'string', 'max:1000'],
            'services' => ['required', 'string', 'max:3000'],
            'services_en' => ['nullable', 'string', 'max:3000'],
            'services_pt' => ['nullable', 'string', 'max:3000'],
        ]);

        SiteSetting::set('press_intro', $data['intro']);
        SiteSetting::set('press_intro_en', $data['intro_en'] ?? '');
        SiteSetting::set('press_intro_pt', $data['intro_pt'] ?? '');
        SiteSetting::setJson('press_services', $this->parseLines($data['services']));
        SiteSetting::setJson('press_services_en', $this->parseLines($data['services_en'] ?? ''));
        SiteSetting::setJson('press_services_pt', $this->parseLines($data['services_pt'] ?? ''));

        return redirect()->route('admin.settings.press')
            ->with('success', 'Page Relations presse mise à jour.');
    }

    public function editFactChecking(): View
    {
        $defaults = config('collectinfos.fact_checking', []);

        return view('admin.settings.fact-checking', [
            'intro' => SiteSetting::get('fact_checking_intro', $defaults['intro'] ?? ''),
            'introEn' => SiteSetting::get('fact_checking_intro_en', ''),
            'introPt' => SiteSetting::get('fact_checking_intro_pt', ''),
            'cta' => SiteSetting::get('fact_checking_cta', 'Vous avez une information à vérifier ?'),
            'ctaEn' => SiteSetting::get('fact_checking_cta_en', ''),
            'ctaPt' => SiteSetting::get('fact_checking_cta_pt', ''),
            'criteria' => $this->padCriteria(SiteSetting::json('fact_checking_criteria', $defaults['criteria'] ?? [])),
            'criteriaEn' => $this->padCriteria(SiteSetting::json('fact_checking_criteria_en', [])),
            'criteriaPt' => $this->padCriteria(SiteSetting::json('fact_checking_criteria_pt', [])),
        ]);
    }

    public function updateFactChecking(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'intro' => ['required', 'string', 'max:1000'],
            'intro_en' => ['nullable', 'string', 'max:1000'],
            'intro_pt' => ['nullable', 'string', 'max:1000'],
            'cta' => ['required', 'string', 'max:200'],
            'cta_en' => ['nullable', 'string', 'max:200'],
            'cta_pt' => ['nullable', 'string', 'max:200'],
            'criteria' => ['required', 'array', 'min:1'],
            'criteria.*.title' => ['required', 'string', 'max:120'],
            'criteria.*.text' => ['required', 'string', 'max:500'],
            'criteria_en' => ['nullable', 'array'],
            'criteria_en.*.title' => ['nullable', 'string', 'max:120'],
            'criteria_en.*.text' => ['nullable', 'string', 'max:500'],
            'criteria_pt' => ['nullable', 'array'],
            'criteria_pt.*.title' => ['nullable', 'string', 'max:120'],
            'criteria_pt.*.text' => ['nullable', 'string', 'max:500'],
        ]);

        SiteSetting::set('fact_checking_intro', $data['intro']);
        SiteSetting::set('fact_checking_intro_en', $data['intro_en'] ?? '');
        SiteSetting::set('fact_checking_intro_pt', $data['intro_pt'] ?? '');
        SiteSetting::set('fact_checking_cta', $data['cta']);
        SiteSetting::set('fact_checking_cta_en', $data['cta_en'] ?? '');
        SiteSetting::set('fact_checking_cta_pt', $data['cta_pt'] ?? '');
        SiteSetting::setJson('fact_checking_criteria', $this->filterCriteria($data['criteria']));
        SiteSetting::setJson('fact_checking_criteria_en', $this->filterCriteria($data['criteria_en'] ?? []));
        SiteSetting::setJson('fact_checking_criteria_pt', $this->filterCriteria($data['criteria_pt'] ?? []));

        return redirect()->route('admin.settings.fact-checking')
            ->with('success', 'Page Fact-checking mise à jour.');
    }

    private function parseLines(string $text): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $text))
            ->map(fn (string $line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    private function filterCriteria(array $items): array
    {
        return collect($items)
            ->map(fn (array $item) => [
                'title' => trim($item['title'] ?? ''),
                'text' => trim($item['text'] ?? ''),
            ])
            ->filter(fn (array $item) => $item['title'] !== '' && $item['text'] !== '')
            ->values()
            ->all();
    }

    private function padCriteria(array $items): array
    {
        while (count($items) < 3) {
            $items[] = ['title' => '', 'text' => ''];
        }

        return $items;
    }
}
