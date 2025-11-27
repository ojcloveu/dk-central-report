<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DkApiService
{
  /**
   * Base URL for DK API
   */
  private string $baseUrl;

  /**
   * Authorization token for DK API
   */
  private string $token;

  /**
   * Constructor
   */
  public function __construct()
  {
    $this->baseUrl = config('services.dk.dk_url');
    $this->token = config('services.dk.dk_token');

    if (empty($this->baseUrl) || empty($this->token)) {
      Log::warning('DK API credentials not configured in services.php');
    }
  }

  /**
   * Fetch summary data (deposit/withdraw) for given usernames
   * 
   * @param array $usernames Array of unique account usernames
   * @return array Response data from DK API
   */
  public function getSummary(array $usernames): array
  {
    if (empty($usernames)) {
      throw new \InvalidArgumentException('Usernames array cannot be empty');
    }

    if (empty($this->baseUrl) || empty($this->token)) {
      throw new \Exception('DK API credentials not configured');
    }

    try {
      // Build query param
      $queryParams = [];
      foreach ($usernames as $username) {
        $queryParams[] = 'username[]=' . urlencode($username);
      }
      $queryString = implode('&', $queryParams);

      $url = rtrim($this->baseUrl, '/') . '/api/report/summary?' . $queryString;

      // Make HTTP request with authorization header
      $response = Http::withHeaders([
        'Content-Type' => 'application/json',
        'Authorization' => $this->token,
      ])->get($url);

      if ($response->failed()) {
        Log::error('DK API request failed', [
          'status' => $response->status(),
          'body' => $response->body()
        ]);
        throw new \Exception('DK API request failed with status: ' . $response->status());
      }

      $data = $response->json();

      return $data;

    } catch (\Exception $e) {
      Log::error('Error fetching DK API summary: ' . $e->getMessage(), [
        'usernames' => $usernames,
        'exception' => $e
      ]);
      throw $e;
    }
  }
}
