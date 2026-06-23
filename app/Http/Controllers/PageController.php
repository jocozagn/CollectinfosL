<?php

namespace App\Http\Controllers;

use App\Models\SiteProduct;
use App\Models\SiteSetting;
use Illuminate\View\View;

class PageController extends Controller
{
    public function products(): View
    {
        return view('pages.products', [
            'products' => SiteProduct::activeList(),
        ]);
    }

    public function press(): View
    {
        return view('pages.press', [
            'press' => SiteSetting::press(),
            'contact' => SiteSetting::contact(),
            'pressTopics' => config('collectinfos.press.topics', []),
            'pressCountries' => config('collectinfos.press.countries', []),
        ]);
    }

    public function factChecking(): View
    {
        return view('pages.fact-checking', [
            'factChecking' => SiteSetting::factChecking(),
        ]);
    }
}
