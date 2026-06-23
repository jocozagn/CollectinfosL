<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    public function run(): void
    {
        $logos = config('collectinfos.partners', []);
        $order = 1;

        foreach ($logos as $logo) {
            Partner::query()->updateOrCreate(
                ['logo' => $logo],
                [
                    'name' => 'Média partenaire',
                    'sort_order' => $order++,
                    'is_active' => true,
                ]
            );
        }
    }
}
