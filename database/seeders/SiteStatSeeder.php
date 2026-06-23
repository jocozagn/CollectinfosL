<?php

namespace Database\Seeders;

use App\Models\SiteStat;
use Illuminate\Database\Seeder;

class SiteStatSeeder extends Seeder
{
    public function run(): void
    {
        $stats = [
            ['value' => 318, 'label' => 'Corresponants', 'sort_order' => 1],
            ['value' => 18, 'label' => 'Pays', 'sort_order' => 2],
            ['value' => 43, 'label' => 'Medias partenaires', 'sort_order' => 3],
        ];

        foreach ($stats as $stat) {
            SiteStat::query()->updateOrCreate(
                ['label' => $stat['label']],
                array_merge($stat, ['is_active' => true])
            );
        }
    }
}
