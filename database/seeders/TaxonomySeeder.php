<?php

namespace Database\Seeders;

use App\Models\Taxonomy;
use Illuminate\Database\Seeder;

class TaxonomySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Experts', 'slug' => 'experts', 'image' => 'https://collectinfos.org/wp-content/uploads/2023/11/5542330-Recuperefv-267x300.png', 'sort_order' => 1],
            ['name' => 'Particuliers', 'slug' => 'particuliers', 'image' => 'https://collectinfos.org/wp-content/uploads/2023/11/5542330-Recuperekjk-267x300.png', 'sort_order' => 2],
            ['name' => 'Médias', 'slug' => 'medias', 'image' => 'https://collectinfos.org/wp-content/uploads/2023/11/5542330-Recuperetf-267x300.png', 'sort_order' => 3],
            ['name' => 'Organisations', 'slug' => 'organisations', 'image' => 'https://collectinfos.org/wp-content/uploads/2023/11/hb1-267x300.png', 'sort_order' => 4],
        ];

        foreach ($categories as $item) {
            Taxonomy::query()->updateOrCreate(
                ['kind' => Taxonomy::KIND_CATEGORY, 'slug' => $item['slug']],
                array_merge($item, ['show_on_home' => true, 'is_active' => true])
            );
        }

        $themes = [
            ['name' => 'Politique', 'slug' => 'politique', 'sort_order' => 1],
            ['name' => 'Économie', 'slug' => 'economie', 'sort_order' => 2],
            ['name' => 'Société', 'slug' => 'societe', 'sort_order' => 3],
            ['name' => 'Sécurité', 'slug' => 'securite', 'sort_order' => 4],
            ['name' => 'Culture', 'slug' => 'culture', 'sort_order' => 5],
            ['name' => 'Environnement', 'slug' => 'environnement', 'sort_order' => 6],
        ];

        foreach ($themes as $item) {
            Taxonomy::query()->updateOrCreate(
                ['kind' => Taxonomy::KIND_THEME, 'slug' => $item['slug']],
                array_merge($item, ['is_active' => true])
            );
        }

        $types = [
            ['name' => 'Vidéo', 'slug' => 'video', 'icon' => 'fa-film', 'sort_order' => 1],
            ['name' => 'Article', 'slug' => 'article', 'icon' => 'fa-newspaper', 'sort_order' => 2],
            ['name' => 'Audio', 'slug' => 'audio', 'icon' => 'fa-headphones', 'sort_order' => 3],
            ['name' => 'Photo', 'slug' => 'photo', 'icon' => 'fa-camera', 'sort_order' => 4],
            ['name' => 'Infographie', 'slug' => 'infographic', 'icon' => 'fa-chart-pie', 'sort_order' => 5],
        ];

        foreach ($types as $item) {
            Taxonomy::query()->updateOrCreate(
                ['kind' => Taxonomy::KIND_TYPE, 'slug' => $item['slug']],
                array_merge($item, ['is_active' => true])
            );
        }
    }
}
