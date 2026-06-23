<?php

namespace Database\Seeders;

use App\Models\Investigation;
use Illuminate\Database\Seeder;

class InvestigationSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'title' => 'Migrations climatiques en Afrique de l\'Ouest',
                'summary' => 'Enquête transfrontalière sur les déplacements de populations liés au changement climatique au Sahel et en Guinée.',
                'country' => 'Guinée / Mali',
                'theme' => 'environnement',
                'places' => 4,
            ],
            [
                'title' => 'Économie informelle et jeunesse à Conakry',
                'summary' => 'Reportage sur les travailleurs du secteur informel et les initiatives économiques des jeunes en milieu urbain.',
                'country' => 'Guinée',
                'theme' => 'economie',
                'places' => 3,
            ],
            [
                'title' => 'Désinformation électorale — veille fact-checking',
                'summary' => 'Cellule de vérification des faits autour des prochaines échéances électorales en Afrique de l\'Ouest.',
                'country' => 'Multi-pays',
                'theme' => 'politique',
                'places' => 5,
            ],
        ];

        foreach ($items as $item) {
            Investigation::query()->updateOrCreate(
                ['title' => $item['title']],
                array_merge($item, [
                    'slug' => \Illuminate\Support\Str::slug($item['title']),
                    'status' => 'open',
                    'published_at' => now(),
                ])
            );
        }
    }
}
