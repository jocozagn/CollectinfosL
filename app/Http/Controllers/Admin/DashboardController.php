<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollaborationRequest;
use App\Models\ContactMessage;
use App\Models\Content;
use App\Models\Investigation;
use App\Models\NewsletterSubscriber;
use App\Models\Partner;
use App\Models\SiteProduct;
use App\Models\SiteStat;
use App\Models\Taxonomy;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'totalContents' => Content::count(),
            'publishedContents' => Content::where('status', 'published')->count(),
            'draftContents' => Content::where('status', 'draft')->count(),
            'pendingRequests' => CollaborationRequest::where('status', 'pending')->count(),
            'contactMessages' => ContactMessage::count(),
            'newsletterCount' => NewsletterSubscriber::count(),
            'openInvestigations' => Investigation::where('status', 'open')->count(),
            'categoryCount' => Taxonomy::where('kind', Taxonomy::KIND_CATEGORY)->count(),
            'themeCount' => Taxonomy::where('kind', Taxonomy::KIND_THEME)->count(),
            'typeCount' => Taxonomy::where('kind', Taxonomy::KIND_TYPE)->count(),
            'siteStatsCount' => SiteStat::count(),
            'partnerCount' => Partner::count(),
            'productCount' => SiteProduct::count(),
            'recentContents' => Content::latest()->take(5)->get(),
            'recentMessages' => ContactMessage::latest()->take(5)->get(),
            'recentRequests' => CollaborationRequest::with('investigation')->latest()->take(5)->get(),
        ]);
    }
}
