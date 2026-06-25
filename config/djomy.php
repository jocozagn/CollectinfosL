<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Djomy Payment API
    | Documentation: https://developers.djomy.africa/
    |--------------------------------------------------------------------------
    */

    'enabled' => env('DJOMY_ENABLED', false),

    'base_url' => env('DJOMY_BASE_URL', 'https://api.djomy.africa'),

    'client_id' => env('DJOMY_CLIENT_ID'),

    'client_secret' => env('DJOMY_CLIENT_SECRET'),

    /*
     * Obligatoire sur toutes les requêtes vers Djomy (fourni par le partenaire).
     */
    'partner_domain' => env('DJOMY_PARTNER_DOMAIN'),

    'country_code' => env('DJOMY_COUNTRY_CODE', 'GN'),

    'currency' => env('DJOMY_CURRENCY', 'GNF'),

    /*
     * Conversion : les prix catalogue sont en EUR, Djomy encaisse en GNF.
     * Voir collectinfos.currency.eur_to_gnf (EUR_TO_GNF_RATE dans .env).
     */

    'verify_ssl' => env('DJOMY_VERIFY_SSL', true),

    /*
     * URLs de retour/annulation — Djomy exige HTTPS (refuse http://localhost).
     * En local : tunnel ngrok ou URL du site en production.
     */
    'callback_base_url' => env('DJOMY_CALLBACK_BASE_URL', env('APP_URL')),

    'webhook_signature_header' => 'x-webhook-signature',

    'method_map' => [
        'orange_money' => 'OM',
        'wave' => 'WAVE',
        'mtn' => 'MOMO',
        'card' => 'CARD',
    ],

];
