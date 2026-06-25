<?php

namespace App\Console\Commands;

use App\Services\Djomy\DjomyClient;
use Illuminate\Console\Command;

class DjomyTestConnection extends Command
{
    protected $signature = 'djomy:test';

    protected $description = 'Teste la connexion à l\'API Djomy (auth)';

    public function handle(DjomyClient $client): int
    {
        if (! $client->isConfigured()) {
            $this->error('Djomy n\'est pas configuré (DJOMY_ENABLED, CLIENT_ID, SECRET, PARTNER_DOMAIN).');

            return self::FAILURE;
        }

        try {
            $token = $client->testAuthentication();

            $this->info('Connexion Djomy OK.');
            $this->line('Base URL : '.config('djomy.base_url'));
            $this->line('Token obtenu ('.strlen($token).' caractères).');

            $callbackBase = rtrim((string) config('djomy.callback_base_url', config('app.url')), '/');

            if (! str_starts_with($callbackBase, 'https://')) {
                $this->warn('DJOMY_CALLBACK_BASE_URL doit être en HTTPS — les paiements échoueront sans cela.');
                $this->line('Ex. DJOMY_CALLBACK_BASE_URL=https://collectinfos.org');
            } else {
                $this->line('Callback HTTPS : '.$callbackBase);
            }

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error('Échec : '.$exception->getMessage());

            if ($previous = $exception->getPrevious()) {
                $this->line($previous->getMessage());
            }

            return self::FAILURE;
        }
    }
}
