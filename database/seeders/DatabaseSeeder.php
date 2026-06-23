<?php

namespace Database\Seeders;

use App\Models\Content;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@collectinfos.org'],
            [
                'name' => 'Administrateur Collectinfos',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'demo@collectinfos.org'],
            [
                'name' => 'Utilisateur Démo',
                'password' => Hash::make('demo123'),
                'role' => 'user',
            ]
        );

        foreach (config('collectinfos.reportages', []) as $item) {
            $title = $item['title'];
            $price = $item['price'] ?? null;
            $isPaid = $price && (float) $price > 0;
            $type = str_contains(strtolower($title), 'video') || str_contains(strtolower($title), 'vidéo')
                ? 'video'
                : 'article';

            Content::query()->updateOrCreate(
                ['slug' => $item['slug']],
                [
                    'title' => $title,
                    'thumbnail' => $item['image'],
                    'type' => $type,
                    'category' => 'medias',
                    'access' => $isPaid ? 'subscriber' : 'free',
                    'price' => $price,
                    'status' => 'published',
                    'published_at' => now(),
                    'summary' => 'Découvrez un extrait de ce reportage Collectinfos sur l\'actualité panafricaine.',
                    'preview_enabled' => true,
                    'preview_seconds' => 15,
                    'preview_excerpt' => 'Extrait : '.Str::limit($title, 120).' — Connectez-vous pour accéder au contenu complet sur Collectinfos.',
                ]
            );
        }

        $this->call(InvestigationSeeder::class);
        $this->call(TaxonomySeeder::class);
        $this->call(SiteStatSeeder::class);
        $this->call(PartnerSeeder::class);
        $this->call(SiteProductSeeder::class);
        $this->call(SiteSettingSeeder::class);
    }
}
