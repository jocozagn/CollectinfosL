<?php

namespace Database\Seeders;

use App\Models\SiteProduct;
use Illuminate\Database\Seeder;

class SiteProductSeeder extends Seeder
{
    public function run(): void
    {
        foreach (config('collectinfos.products', []) as $index => $product) {
            $isMonthly = str_contains(strtolower($product['name'] ?? ''), 'mensuel');
            SiteProduct::query()->updateOrCreate(
                ['name' => $product['name']],
                [
                    'description' => $product['description'],
                    'price' => $product['price'],
                    'icon' => $product['icon'] ?? 'fa-newspaper',
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'is_subscribable' => $isMonthly,
                    'price_eur' => $isMonthly ? 29 : null,
                    'price_gnf' => $isMonthly ? 290000 : null,
                    'billing_months' => 1,
                    'discount_percent' => 10,
                ]
            );
        }
    }
}
