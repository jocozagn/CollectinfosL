<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Partner;
use App\Models\SiteSetting;
use App\Models\SiteStat;
use App\Models\Taxonomy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $categoryFilter = $request->query('flux', 'all');

        return view('home', [
            'categories' => Taxonomy::homeCategories(),
            'reportages' => $this->getReportages(),
            'fluxItems' => $this->getFluxItems($categoryFilter),
            'fluxFilter' => $categoryFilter,
            'fluxCategories' => [
                'all' => 'Tout',
                'experts' => 'Experts',
                'particuliers' => 'Particuliers',
                'medias' => 'Médias',
                'organisations' => 'Organisations',
            ],
            'videoExclusives' => $this->getVideoExclusives(),
            'stats' => SiteStat::activeList(),
            'partners' => Partner::activeLogos(),
            'contact' => SiteSetting::contact(),
        ]);
    }

    private function getReportages(): array
    {
        try {
            $contents = Content::published()->latest('published_at')->take(12)->get();

            if ($contents->isNotEmpty()) {
                return $contents->map(fn (Content $c) => $c->toCardArray())->all();
            }
        } catch (\Throwable) {
        }

        return collect(config('collectinfos.reportages', []))
            ->map(fn (array $item) => $this->normalizeConfigReportage($item))
            ->all();
    }

    private function getFluxItems(string $category): array
    {
        try {
            $query = Content::published()->latest('published_at')->take(8);

            if ($category !== 'all') {
                $query->where('category', $category);
            }

            $contents = $query->get();

            if ($contents->isNotEmpty()) {
                return $contents->map(fn (Content $c) => $c->toCardArray())->all();
            }
        } catch (\Throwable) {
        }

        return array_slice($this->getReportages(), 0, 8);
    }

    private function getVideoExclusives(): array
    {
        try {
            $videos = Content::published()
                ->where('type', 'video')
                ->where(function ($q) {
                    $q->where('access', 'exclusive')
                        ->orWhere('price', '>', 0);
                })
                ->latest('published_at')
                ->take(2)
                ->get();

            if ($videos->isNotEmpty()) {
                return $videos->map(fn (Content $c) => $c->toCardArray())->all();
            }
        } catch (\Throwable) {
        }

        return array_slice(
            array_filter($this->getReportages(), fn ($item) => ($item['type'] ?? '') === 'video'),
            0,
            2
        );
    }

    private function normalizeConfigReportage(array $item): array
    {
        $price = $item['price'] ?? null;
        $isPaid = $price && (float) $price > 0;
        $type = $item['type'] ?? (str_contains(strtolower($item['title']), 'video') ? 'video' : 'article');

        return array_merge([
            'type' => $type,
            'type_label' => Content::typeLabels()[$type] ?? 'Article',
            'access' => $item['access'] ?? 'free',
            'is_free' => ! $isPaid,
            'is_paid' => $isPaid,
            'preview_enabled' => true,
            'preview_seconds' => 15,
            'preview_mode' => $type === 'video' ? 'text' : 'text',
            'preview_text' => $item['preview_text'] ?? 'Aperçu disponible — connectez-vous pour le contenu complet.',
            'preview_url' => $item['preview_url'] ?? null,
            'preview_embed' => null,
        ], $item);
    }
}
