<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Partner;
use App\Models\SiteSetting;
use App\Models\SiteStat;
use App\Models\Taxonomy;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'categories' => Taxonomy::homeCategories(),
            'reportages' => $this->getReportages(),
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
            // Base de données non configurée : repli sur la config statique
        }

        return collect(config('collectinfos.reportages', []))
            ->map(fn (array $item) => $this->normalizeConfigReportage($item))
            ->all();
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
