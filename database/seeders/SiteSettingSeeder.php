<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $contact = config('collectinfos.contact', []);

        SiteSetting::set('contact_phone', $contact['phone'] ?? '');
        SiteSetting::set('contact_email', $contact['email'] ?? '');
        SiteSetting::set('contact_zone', "Afrique de l'Ouest et panafrique — réseau de correspondants dans 18 pays.");

        $press = config('collectinfos.press', []);
        SiteSetting::set('press_intro', $press['intro'] ?? '');
        SiteSetting::setJson('press_services', $press['services'] ?? []);

        $factChecking = config('collectinfos.fact_checking', []);
        SiteSetting::set('fact_checking_intro', $factChecking['intro'] ?? '');
        SiteSetting::setJson('fact_checking_criteria', $factChecking['criteria'] ?? []);
        SiteSetting::set('fact_checking_cta', 'Vous avez une information à vérifier ?');
    }
}
