<?php

namespace Database\Seeders;

use App\Models\SiteProduct;
use Illuminate\Database\Seeder;

class SiteProductSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('collectinfos.products', []) as $index => $product) {
            SiteProduct::query()->updateOrCreate(
                ['name' => $product['name']],
                [
                    'description' => $product['description'],
                    'price' => $product['price'],
                    'icon' => $product['icon'] ?? 'fa-newspaper',
                    'sort_order' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
