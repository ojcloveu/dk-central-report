<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

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

        return $this->accessToken;
    }

    public function get(string $endpoint, array $params = [])
    {
        return Http::timeout(config('services.mlm.api_timeout'))
            ->withToken($this->getAccessToken())
            ->get(config('services.mlm.api_url') . $endpoint, $params)
            ->throw()
            ->json();
    }

    public function post(string $endpoint, array $payload = [])
    {
        return Http::timeout(config('services.mlm.api_timeout'))
            ->withToken($this->getAccessToken())
            ->post(config('services.mlm.api_url') . $endpoint, $payload)
            ->throw()
            ->json();
    }
}
