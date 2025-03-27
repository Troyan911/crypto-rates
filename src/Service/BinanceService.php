<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BinanceService
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function fetchHistoricalData(string $symbol, string $interval, int $startTime, int $endTime): array
    {
        $url = "https://api.binance.com/api/v3/klines?symbol={$symbol}&interval={$interval}&startTime={$startTime}&endTime={$endTime}";
        $response = $this->client->request('GET', $url);

        return $response->toArray();
    }
}
