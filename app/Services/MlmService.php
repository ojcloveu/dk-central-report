<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MlmService
{
    protected string $accessToken;
    protected Carbon $tokenExpiry;

    protected function getAccessToken(): string
    {
        if (isset($this->accessToken) &&
            Carbon::now()->lessThan($this->tokenExpiry)) {
            return $this->accessToken;
        }

        $cachedToken = Cache::get('mlm:access_token');
        if ($cachedToken) {
            $this->accessToken = $cachedToken['token'];
            $this->tokenExpiry = Carbon::parse($cachedToken['expires_at']);

            if (Carbon::now()->lessThan($this->tokenExpiry)) {
                return $this->accessToken;
            }
        }

        $response = Http::asForm()->post(config('services.mlm.api_url') . '/oauth/token', [
            'grant_type' => 'client_credentials',
            'client_id' => config('services.mlm.client_id'),
            'client_secret' => config('services.mlm.client_secret'),
            'scope' => '*',
        ]);

        if ($response->failed()) {
            throw new \RuntimeException(
                'Failed to retrieve access token: ' . $response->body()
            );
        }

        $data = $response->json();

        $this->accessToken = $data['access_token'];
        $this->tokenExpiry = Carbon::now()->addSeconds($data['expires_in'] - 60);

        Cache::put('mlm:access_token', [
            'token' => $this->accessToken,
            'expires_at' => $this->tokenExpiry,
        ], $data['expires_in'] - 60);

        return $this->accessToken;
    }

    /**
     * Normalize endpoint - ensures leading slash
     * For example:
     * 'api/report/summary' → '/api/report/summary'
     * '/api/report/summary' → '/api/report/summary'
     */
    protected function normalizeEndpoint(string $endpoint): string
    {
        return '/' . ltrim($endpoint, '/');
    }

    public function get(string $endpoint, array $params = [])
    {
        $normalizedEndpoint = $this->normalizeEndpoint($endpoint);

        return Http::timeout(config('services.mlm.api_timeout'))
            ->withToken($this->getAccessToken())
            ->get(config('services.mlm.api_url') . $normalizedEndpoint, $params)
            ->throw()
            ->json();
    }

    public function post(string $endpoint, array $payload = [])
    {
        $normalizedEndpoint = $this->normalizeEndpoint($endpoint);

        return Http::timeout(config('services.mlm.api_timeout'))
            ->withToken($this->getAccessToken())
            ->post(config('services.mlm.api_url') . $normalizedEndpoint, $payload)
            ->throw()
            ->json();
    }

    /**
     * Clear token cache (useful for testing/debugging)
     */
    public function clearTokenCache(): void
    {
        Cache::forget('mlm:access_token');
        $this->accessToken = '';
        $this->tokenExpiry = Carbon::now()->subDay();
    }

    /**
     * Get token expiry info (for debugging)
     */
    public function getTokenInfo(): array
    {
        return [
            'token_exists' => isset($this->accessToken),
            'expires_at' => $this->tokenExpiry ?? null,
            'seconds_left' => $this->tokenExpiry ?
                $this->tokenExpiry->diffInSeconds(Carbon::now()) : 0,
            'is_valid' => isset($this->accessToken) &&
                         Carbon::now()->lessThan($this->tokenExpiry),
        ];
    }
}
