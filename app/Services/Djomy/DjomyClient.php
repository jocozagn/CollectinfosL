<?php

namespace App\Services\Djomy;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class DjomyClient
{
    public function isConfigured(): bool
    {
        return config('djomy.enabled')
            && config('djomy.client_id')
            && config('djomy.client_secret')
            && config('djomy.partner_domain');
    }

    public function createPaymentGateway(array $payload): array
    {
        return $this->request('POST', '/v1/payments/gateway', $payload);
    }

    public function getPaymentStatus(string $transactionId): array
    {
        return $this->request('GET', '/v1/payments/'.$transactionId.'/status');
    }

    public function verifyWebhookSignature(string $rawBody, string $signatureHeader): bool
    {
        if (! str_starts_with($signatureHeader, 'v1:')) {
            return false;
        }

        $received = substr($signatureHeader, 3);
        $expected = hash_hmac('sha256', $rawBody, (string) config('djomy.client_secret'));

        return hash_equals($expected, $received);
    }

    public function testAuthentication(): string
    {
        Cache::forget('djomy_access_token');

        return $this->accessToken();
    }

    private function request(string $method, string $path, array $body = []): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Djomy n\'est pas configuré. Vérifiez les variables DJOMY_* dans .env');
        }

        $request = $this->http()->withToken($this->accessToken());

        $response = $method === 'GET'
            ? $request->get($this->url($path))
            : $request->post($this->url($path), $body);

        $json = $response->json();

        if (! $response->successful() || ! ($json['success'] ?? false)) {
            $message = $json['error']['message'] ?? $json['message'] ?? $response->body();
            $details = $json['errors'] ?? $json['error']['fieldsErrors'] ?? null;

            if (is_array($details) && $details !== []) {
                $message .= ' — '.implode(' ; ', array_map('strval', $details));
            }

            throw new RuntimeException('Djomy API : '.$message);
        }

        return $json['data'] ?? [];
    }

    private function accessToken(): string
    {
        return Cache::remember('djomy_access_token', 50 * 60, function () {
            $clientId = (string) config('djomy.client_id');
            $signature = $this->signature();

            $response = $this->http()
                ->withBody('{}', 'application/json')
                ->post($this->url('/v1/auth'));

            $json = $response->json();

            if (! $response->successful() || ! ($json['success'] ?? false)) {
                $detail = is_array($json)
                    ? json_encode($json, JSON_UNESCAPED_UNICODE)
                    : $response->body();

                throw new RuntimeException('Djomy auth échouée (HTTP '.$response->status().') : '.$detail);
            }

            return $json['data']['accessToken'] ?? throw new RuntimeException('Djomy auth : token manquant');
        });
    }

    private function http(): PendingRequest
    {
        $request = Http::withHeaders($this->defaultHeaders())
            ->acceptJson()
            ->asJson()
            ->timeout(30)
            ->withUserAgent('Collectinfos/1.0 (+'.config('app.url').')');

        if (! config('djomy.verify_ssl', true)) {
            $request = $request->withOptions(['verify' => false]);
        }

        return $request;
    }

    private function defaultHeaders(): array
    {
        return [
            'X-PARTNER-DOMAIN' => (string) config('djomy.partner_domain'),
            'X-API-KEY' => config('djomy.client_id').':'.$this->signature(),
        ];
    }

    private function signature(): string
    {
        return hash_hmac(
            'sha256',
            (string) config('djomy.client_id'),
            (string) config('djomy.client_secret')
        );
    }

    private function url(string $path): string
    {
        return rtrim((string) config('djomy.base_url'), '/').$path;
    }
}
